<?php

namespace Tests\Feature\LanRelay;

use App\Models\Appointment;
use App\Models\Group;
use App\Models\LanRelayEvent;
use App\Models\ReceptionTerminal;
use App\Models\ScheduleEvent;
use App\Models\Setting;
use App\Models\User;
use App\Services\LanRelay\LanRelayEventRecorder;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

/**
 * 受付4経路の来客イベント記録（§6.2・§7）
 */
class ReceptionEventRecordingTest extends LanRelayTestCase
{
    private User $staff;
    private Group $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->group = Group::create(['name' => '営業部']);
        $this->staff = User::forceCreate(['name' => '山田太郎', 'email' => 'yamada@example.test', 'group_id' => $this->group->id]);

        ReceptionTerminal::create(['number' => 1, 'name' => '新社屋 来客玄関', 'camera_src' => 'nvr3', 'enabled' => true]);
        ReceptionTerminal::create(['number' => 2, 'name' => '新社屋 業者入口', 'camera_src' => 'cam1', 'enabled' => true]);
        ReceptionTerminal::create(['number' => 3, 'name' => '旧社屋 事務所入口', 'camera_src' => 'cam2', 'enabled' => false]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function makeAppointment(array $attributes = []): Appointment
    {
        return Appointment::create($attributes + [
            'reception_number' => '1234',
            'company_name' => '株式会社テスト',
            'visitor_name' => '来客 花子',
            'staff_member_id' => $this->staff->id,
            'is_checked_in' => false,
        ]);
    }

    // ── 4経路とも1件記録（通知設定なし） ─────────────────────────

    public function test_qr_check_in_records_one_event_without_notification_settings(): void
    {
        $appointment = $this->makeAppointment();

        $this->post(route('appointment.check-in-qr'), [
            'qr_data' => json_encode(['visitor_id' => $appointment->id]),
            'terminal' => 1,
        ])->assertRedirect(route('visitor.complete'));

        $this->assertSame(1, LanRelayEvent::count());
        $event = LanRelayEvent::first();
        $this->assertSame('appointment', $event->type);
        $this->assertSame(1, $event->terminal);
        $this->assertSame('nvr3', $event->camera);
        $this->assertSame('ご来客（アポイントあり）株式会社テスト 様 → 担当：山田太郎', $event->message);
        $this->assertFalse($event->suppressed);
        $this->assertNull($event->suppress_reason);
    }

    public function test_reception_number_check_in_records_one_event(): void
    {
        $this->makeAppointment();

        $this->post(route('appointment.check-in-number'), [
            'reception_number' => '1234',
            'terminal' => 2,
        ])->assertRedirect(route('visitor.complete'));

        $this->assertSame(1, LanRelayEvent::count());
        $event = LanRelayEvent::first();
        $this->assertSame('appointment', $event->type);
        $this->assertSame('cam1', $event->camera);
    }

    public function test_today_schedule_arrival_records_one_event(): void
    {
        $schedule = ScheduleEvent::create([
            'title' => '打合せ',
            'organizer_name' => '鈴木一郎',
            'organizer_email' => 'suzuki@example.test',
            'date' => now()->toDateString(),
        ]);

        $this->post(route('appointment.today.notify', $schedule->id), ['terminal' => 1])
            ->assertRedirect(route('appointment.today'));

        $this->assertSame(1, LanRelayEvent::count());
        $event = LanRelayEvent::first();
        $this->assertSame('appointment', $event->type);
        $this->assertSame('ご来客（アポイントあり） → 担当：鈴木一郎', $event->message);
    }

    public function test_today_schedule_arrival_uses_linked_appointment(): void
    {
        $appointment = $this->makeAppointment();
        $schedule = ScheduleEvent::create([
            'appointment_id' => $appointment->id,
            'title' => '打合せ',
            'organizer_name' => '鈴木一郎',
        ]);

        $this->post(route('appointment.today.notify', $schedule->id), ['terminal' => 1]);

        $this->assertSame('ご来客（アポイントあり）株式会社テスト 様 → 担当：山田太郎', LanRelayEvent::first()->message);
    }

    public function test_walk_in_records_one_event_without_visitor_name(): void
    {
        $this->post(route('other-visitor.store'), [
            'company_name' => '有限会社アポなし',
            'visitor_name' => '個人 名前',
            'number_of_people' => 2,
            'purpose' => '営業',
            'group_id' => $this->group->id,
            'terminal' => 1,
        ])->assertOk();

        $this->assertSame(1, LanRelayEvent::count());
        $event = LanRelayEvent::first();
        $this->assertSame('walk_in', $event->type);
        $this->assertSame('ご来客（アポイントなし）有限会社アポなし 様 → 部署：営業部', $event->message);
        $this->assertSame('有限会社アポなし', $event->company_name);
        $this->assertSame('営業部', $event->staff_name);

        // 訪問者の個人名はどの列にも残さない
        $this->assertStringNotContainsString('個人 名前', json_encode($event->getAttributes(), JSON_UNESCAPED_UNICODE));
    }

    public function test_interview_records_one_event_without_company_or_name(): void
    {
        $this->postJson(route('interview.notify-staff'), ['terminal' => 1])->assertOk();

        $this->assertSame(1, LanRelayEvent::count());
        $event = LanRelayEvent::first();
        $this->assertSame('interview', $event->type);
        $this->assertSame('面接の方がご来社', $event->message);
        $this->assertNull($event->company_name);
        $this->assertNull($event->staff_name);
        $this->assertFalse($event->suppressed);
    }

    public function test_event_is_recorded_before_teams_send(): void
    {
        $countAtSend = null;
        Http::fake(function (HttpRequest $request) use (&$countAtSend) {
            $countAtSend = LanRelayEvent::count();

            return Http::response(['ok' => true], 200);
        });

        $this->post(route('other-visitor.store'), [
            'company_name' => 'A社',
            'visitor_name' => 'X',
            'number_of_people' => 1,
            'purpose' => '用件',
            'group_id' => $this->group->id,
        ])->assertOk();

        Http::assertSentCount(1);
        $this->assertSame(1, $countAtSend);
    }

    public function test_reception_continues_when_recording_fails(): void
    {
        Schema::drop('lan_relay_events');

        $this->postJson(route('interview.notify-staff'), ['terminal' => 1])
            ->assertOk()
            ->assertJson(['status' => 'success']);
    }

    // ── 端末とカメラ ─────────────────────────────────────

    public function test_unset_terminal_uses_default_camera(): void
    {
        $this->postJson(route('interview.notify-staff'))->assertOk();

        $event = LanRelayEvent::first();
        $this->assertNull($event->terminal);
        $this->assertSame('nvr1', $event->camera);
        $this->assertFalse($event->suppressed);
    }

    public function test_unregistered_or_invalid_terminal_uses_default_camera(): void
    {
        $this->postJson(route('interview.notify-staff'), ['terminal' => 9])->assertOk();
        $this->postJson(route('interview.notify-staff'), ['terminal' => 'abc'])->assertOk();

        [$unregistered, $invalid] = LanRelayEvent::orderBy('id')->get()->all();
        $this->assertSame(9, $unregistered->terminal);
        $this->assertSame('nvr1', $unregistered->camera);
        $this->assertNull($invalid->terminal);
        $this->assertSame('nvr1', $invalid->camera);
    }

    public function test_default_camera_setting_overrides_config_when_allowed(): void
    {
        Setting::put(LanRelayEventRecorder::DEFAULT_CAMERA_SETTING, 'nvr10');
        $this->postJson(route('interview.notify-staff'))->assertOk();
        $this->assertSame('nvr10', LanRelayEvent::first()->camera);

        // 許可リスト外の値は無視して config の既定へ
        Setting::put(LanRelayEventRecorder::DEFAULT_CAMERA_SETTING, 'evil');
        $this->assertSame('nvr1', LanRelayEventRecorder::defaultCamera());
    }

    public function test_terminal_header_is_accepted(): void
    {
        $this->postJson(route('interview.notify-staff'), [], ['X-Reception-Terminal' => '2'])->assertOk();

        $this->assertSame('cam1', LanRelayEvent::first()->camera);
    }

    public function test_disabled_terminal_is_recorded_as_suppressed(): void
    {
        $this->post(route('other-visitor.store'), [
            'company_name' => 'B社',
            'visitor_name' => 'Y',
            'number_of_people' => 1,
            'purpose' => '用件',
            'group_id' => $this->group->id,
            'terminal' => 3,
        ])->assertOk();

        $event = LanRelayEvent::first();
        $this->assertTrue($event->suppressed);
        $this->assertSame('terminal_disabled', $event->suppress_reason);
        $this->assertSame('cam2', $event->camera);
    }

    // ── 面接の2分抑止 ────────────────────────────────────

    public function test_interview_within_two_minutes_on_same_terminal_is_suppressed(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');
        $this->postJson(route('interview.notify-staff'), ['terminal' => 1])->assertOk();

        Carbon::setTestNow('2026-10-30 10:01:59');
        $this->postJson(route('interview.notify-staff'), ['terminal' => 1])->assertOk();

        // 別端末は独立
        $this->postJson(route('interview.notify-staff'), ['terminal' => 2])->assertOk();

        Carbon::setTestNow('2026-10-30 10:02:00');
        $this->postJson(route('interview.notify-staff'), ['terminal' => 1])->assertOk();

        $events = LanRelayEvent::orderBy('id')->get();
        $this->assertCount(4, $events);
        $this->assertFalse($events[0]->suppressed);
        $this->assertTrue($events[1]->suppressed);
        $this->assertSame('interview_cooldown', $events[1]->suppress_reason);
        $this->assertFalse($events[2]->suppressed);
        // 2分ちょうど経過で再び鳴らす（抑止された押下では起点を延長しない）
        $this->assertFalse($events[3]->suppressed);
    }

    public function test_interview_cooldown_does_not_apply_to_other_types(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');
        $this->postJson(route('interview.notify-staff'), ['terminal' => 1])->assertOk();

        $this->post(route('other-visitor.store'), [
            'company_name' => 'C社',
            'visitor_name' => 'Z',
            'number_of_people' => 1,
            'purpose' => '用件',
            'group_id' => $this->group->id,
            'terminal' => 1,
        ])->assertOk();

        $this->assertFalse(LanRelayEvent::where('type', 'walk_in')->first()->suppressed);
    }

    // ── 頻度制限（受付側：10秒に3回まで。超過分も記録し抑止） ──────────

    public function test_rate_limit_records_excess_events_as_suppressed(): void
    {
        $this->makeAppointment();
        for ($i = 0; $i < 4; $i++) {
            $this->post(route('appointment.check-in-qr'), [
                'qr_data' => json_encode(['visitor_id' => Appointment::first()->id]),
                'terminal' => 2,
            ])->assertRedirect(route('visitor.complete'));
        }

        $events = LanRelayEvent::orderBy('id')->get();
        $this->assertCount(4, $events);
        $this->assertSame([false, false, false, true], $events->pluck('suppressed')->all());
        $this->assertSame('rate_limited', $events[3]->suppress_reason);
    }

    // ── 表示文字 ───────────────────────────────────────

    public function test_message_is_truncated_to_sixty_characters(): void
    {
        $longCompany = str_repeat('株', 80);
        $message = LanRelayEventRecorder::buildMessage('appointment', $longCompany, '担当');
        $this->assertSame(60, mb_strlen($message));

        // 絵文字（UTF-16で2単位）を含んでも JS の length で60以内
        $emoji = LanRelayEventRecorder::buildMessage('walk_in', str_repeat('😀', 40), null);
        $this->assertLessThanOrEqual(60, strlen(mb_convert_encoding($emoji, 'UTF-16LE', 'UTF-8')) / 2);

        // 改行などの制御文字は空白に
        $this->assertSame(
            'ご来客（アポイントあり）A 社 様 → 担当：B',
            LanRelayEventRecorder::buildMessage('appointment', "A\n社", 'B')
        );
    }
}
