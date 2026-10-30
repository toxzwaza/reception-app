<?php

namespace Tests\Feature\LanRelay;

use App\Models\LanRelayEvent;
use App\Models\ReceptionTerminal;
use App\Models\Setting;
use App\Models\StaffMember;
use App\Models\User;
use App\Services\LanRelay\LanRelayEventRecorder;
use Inertia\Testing\AssertableInertia;

/**
 * 管理画面：受付端末の一覧・追加・編集と既定カメラ
 */
class ReceptionTerminalAdminTest extends LanRelayTestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::forceCreate(['name' => '管理者', 'email' => 'admin@example.test']);
        StaffMember::create(['user_id' => $this->admin->id]);
    }

    private function adminHeaders(): array
    {
        return ['X-User-ID' => (string) $this->admin->id];
    }

    public function test_non_staff_cannot_access(): void
    {
        $this->get(route('admin.reception-terminals.index'))->assertRedirect(route('login'));
    }

    public function test_index_lists_terminals_cameras_and_events(): void
    {
        ReceptionTerminal::create(['number' => 1, 'name' => '新社屋 来客玄関', 'camera_src' => 'nvr3', 'enabled' => true]);

        $this->get(route('admin.reception-terminals.index'), $this->adminHeaders())
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/ReceptionTerminals/Index')
                ->has('terminals', 1)
                ->where('terminals.0.camera_src', 'nvr3')
                ->where('defaultCamera', 'nvr1')
                ->has('cameras', count(config('lan_relay.cameras')))
                ->has('events', 0));
    }

    public function test_store_and_update_terminal(): void
    {
        $this->post(route('admin.reception-terminals.store'), [
            'number' => 4,
            'name' => '新社屋 2F',
            'camera_src' => 'nvr2',
            'enabled' => true,
        ], $this->adminHeaders())->assertRedirect(route('admin.reception-terminals.index'));

        $terminal = ReceptionTerminal::where('number', 4)->firstOrFail();
        $this->assertTrue($terminal->enabled);

        $this->put(route('admin.reception-terminals.update', $terminal->id), [
            'number' => 4,
            'name' => '新社屋 2F（休止）',
            'camera_src' => 'nvr4',
            'enabled' => false,
        ], $this->adminHeaders())->assertRedirect(route('admin.reception-terminals.index'));

        $terminal->refresh();
        $this->assertSame('新社屋 2F（休止）', $terminal->name);
        $this->assertSame('nvr4', $terminal->camera_src);
        $this->assertFalse($terminal->enabled);
    }

    public function test_camera_must_be_in_allowlist_and_number_unique(): void
    {
        ReceptionTerminal::create(['number' => 1, 'name' => 'A', 'camera_src' => 'nvr3', 'enabled' => true]);

        $this->post(route('admin.reception-terminals.store'), [
            'number' => 1,
            'name' => 'B',
            'camera_src' => 'rtsp://evil',
            'enabled' => true,
        ], $this->adminHeaders())->assertSessionHasErrors(['number', 'camera_src']);

        $this->post(route('admin.reception-terminals.store'), [
            'number' => 100,
            'name' => 'C',
            'camera_src' => 'nvr1',
        ], $this->adminHeaders())->assertSessionHasErrors(['number']);

        $this->assertSame(1, ReceptionTerminal::count());
    }

    public function test_update_default_camera(): void
    {
        $this->post(route('admin.reception-terminals.default-camera'), ['default_camera' => 'nvr11'], $this->adminHeaders())
            ->assertRedirect(route('admin.reception-terminals.index'));
        $this->assertSame('nvr11', Setting::get(LanRelayEventRecorder::DEFAULT_CAMERA_SETTING));

        $this->post(route('admin.reception-terminals.default-camera'), ['default_camera' => 'evil'], $this->adminHeaders())
            ->assertSessionHasErrors(['default_camera']);
        $this->assertSame('nvr11', Setting::get(LanRelayEventRecorder::DEFAULT_CAMERA_SETTING));
    }

    public function test_update_chime_volume(): void
    {
        $this->assertSame(60, LanRelayEventRecorder::chimeVolume());   // 未設定は config の 60

        $this->post(route('admin.reception-terminals.chime-volume'), ['chime_volume' => 75], $this->adminHeaders())
            ->assertRedirect(route('admin.reception-terminals.index'));
        $this->assertSame(75, LanRelayEventRecorder::chimeVolume());

        foreach ([101, -1, 'abc', ''] as $invalid) {
            $this->post(route('admin.reception-terminals.chime-volume'), ['chime_volume' => $invalid], $this->adminHeaders())
                ->assertSessionHasErrors(['chime_volume']);
        }
        $this->assertSame(75, LanRelayEventRecorder::chimeVolume());
    }

    public function test_seeder_creates_three_terminals_without_overwriting_edits(): void
    {
        $this->seed(\Database\Seeders\ReceptionTerminalSeeder::class);

        $this->assertSame(
            [1 => 'nvr3', 2 => 'cam1', 3 => 'cam2'],
            ReceptionTerminal::orderBy('number')->pluck('camera_src', 'number')->all()
        );

        ReceptionTerminal::where('number', 1)->update(['camera_src' => 'nvr2']);
        $this->seed(\Database\Seeders\ReceptionTerminalSeeder::class);

        $this->assertSame(3, ReceptionTerminal::count());
        $this->assertSame('nvr2', ReceptionTerminal::where('number', 1)->value('camera_src'));
    }
    public function test_send_test_event_with_defaults(): void
    {
        ReceptionTerminal::create(['number' => 1, 'name' => '新社屋 来客玄関', 'camera_src' => 'nvr3', 'enabled' => true]);

        $this->post(route('admin.reception-terminals.test-event'), [
            'type' => 'appointment',
            'terminal' => 1,
        ], $this->adminHeaders())
            ->assertRedirect(route('admin.reception-terminals.index'))
            ->assertSessionHas('success');

        $event = LanRelayEvent::sole();
        $this->assertSame('appointment', $event->type);
        $this->assertSame(1, $event->terminal);
        $this->assertSame('nvr3', $event->camera);
        $this->assertSame('【テスト】ご来客（アポイントあり）テスト商事株式会社 様 → 担当：受付テスト', $event->message);
        $this->assertFalse($event->suppressed);
    }

    public function test_send_test_event_with_input_and_default_camera(): void
    {
        $this->post(route('admin.reception-terminals.test-event'), [
            'type' => 'walk_in',
            'company_name' => '株式会社サンプル',
            'staff_name' => '',
        ], $this->adminHeaders())->assertRedirect(route('admin.reception-terminals.index'));

        $event = LanRelayEvent::sole();
        $this->assertNull($event->terminal);
        $this->assertSame('nvr1', $event->camera);
        $this->assertSame('株式会社サンプル', $event->company_name);
        $this->assertSame('【テスト】ご来客（アポイントなし）株式会社サンプル 様 → 部署：総務部', $event->message);
    }

    public function test_interview_test_events_skip_cooldown_and_rate_limit(): void
    {
        for ($i = 0; $i < 4; $i++) {
            $this->post(route('admin.reception-terminals.test-event'), [
                'type' => 'interview',
                'company_name' => '入力しても表示しない',
            ], $this->adminHeaders())->assertRedirect(route('admin.reception-terminals.index'));
        }

        $this->assertSame(4, LanRelayEvent::where('suppressed', false)->count());
        $event = LanRelayEvent::first();
        $this->assertSame('【テスト】面接の方がご来社', $event->message);
        $this->assertNull($event->company_name);
    }

    public function test_test_event_on_disabled_terminal_is_suppressed(): void
    {
        ReceptionTerminal::create(['number' => 2, 'name' => '旧受付', 'camera_src' => 'cam1', 'enabled' => false]);

        $this->post(route('admin.reception-terminals.test-event'), [
            'type' => 'appointment',
            'terminal' => 2,
        ], $this->adminHeaders())->assertRedirect(route('admin.reception-terminals.index'));

        $this->assertSame(LanRelayEvent::REASON_TERMINAL_DISABLED, LanRelayEvent::sole()->suppress_reason);
    }

    public function test_test_event_validation(): void
    {
        $this->post(route('admin.reception-terminals.test-event'), [
            'type' => 'unknown',
            'terminal' => 100,
        ], $this->adminHeaders())->assertSessionHasErrors(['type', 'terminal']);

        $this->assertSame(0, LanRelayEvent::count());
    }

    public function test_non_staff_cannot_send_test_event(): void
    {
        $this->post(route('admin.reception-terminals.test-event'), ['type' => 'interview'])
            ->assertRedirect(route('login'));

        $this->assertSame(0, LanRelayEvent::count());
    }
}
