<?php

namespace Tests\Feature\LanRelay;

use App\Models\LanRelayEvent;
use App\Models\Setting;
use App\Services\LanRelay\LanRelayEventRecorder;
use Illuminate\Support\Carbon;

/**
 * 中継役向けAPI（§13.1）と保存期間の削除
 */
class LanRelayApiTest extends LanRelayTestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function makeEvent(array $attributes = []): LanRelayEvent
    {
        return LanRelayEvent::create($attributes + [
            'type' => 'interview',
            'terminal' => 1,
            'camera' => 'nvr3',
            'message' => '面接の方がご来社',
            'occurred_at' => now(),
            'suppressed' => false,
        ]);
    }

    // ── 認証 ────────────────────────────────────────

    public function test_requests_without_or_with_wrong_token_are_401(): void
    {
        $event = $this->makeEvent();

        $this->getJson('/api/lan-relay/events', $this->apiHeaders(null))->assertStatus(401);
        $this->getJson('/api/lan-relay/events', $this->apiHeaders('wrong'))->assertStatus(401);
        $this->postJson("/api/lan-relay/events/{$event->id}/ack", ['display' => 'ok', 'chime' => 'ok'], $this->apiHeaders('wrong'))
            ->assertStatus(401);
        $this->assertNull($event->fresh()->acked_at);
    }

    public function test_always_401_when_token_is_not_configured(): void
    {
        config(['lan_relay.token' => null]);
        $this->getJson('/api/lan-relay/events', $this->apiHeaders(null))->assertStatus(401);
        $this->getJson('/api/lan-relay/events', ['Authorization' => 'Bearer '])->assertStatus(401);

        config(['lan_relay.token' => '']);
        $this->getJson('/api/lan-relay/events', ['Authorization' => 'Bearer '])->assertStatus(401);
    }

    public function test_unauthorized_response_is_json_even_without_accept_header(): void
    {
        $this->get('/api/lan-relay/events')->assertStatus(401)->assertJson(['message' => 'Unauthorized.']);
    }

    // ── 取得 ────────────────────────────────────────

    public function test_without_after_returns_only_latest_id(): void
    {
        $this->makeEvent();
        $last = $this->makeEvent();

        $this->getJson('/api/lan-relay/events', $this->apiHeaders())
            ->assertOk()
            ->assertJsonPath('latest_id', $last->id)
            ->assertJsonPath('events', [])
            ->assertJsonStructure(['latest_id', 'server_time', 'events']);
    }

    public function test_settings_include_chime_volume(): void
    {
        $this->getJson('/api/lan-relay/events', $this->apiHeaders())
            ->assertOk()
            ->assertJsonPath('settings.chime_volume', 60);

        Setting::put(LanRelayEventRecorder::CHIME_VOLUME_SETTING, '75');

        $this->getJson('/api/lan-relay/events?after=0', $this->apiHeaders())
            ->assertOk()
            ->assertJsonPath('settings.chime_volume', 75);
    }

    public function test_latest_id_is_zero_when_empty(): void
    {
        $this->getJson('/api/lan-relay/events', $this->apiHeaders())
            ->assertOk()
            ->assertJsonPath('latest_id', 0)
            ->assertJsonPath('events', []);
    }

    public function test_after_and_limit(): void
    {
        $ids = [];
        for ($i = 0; $i < 5; $i++) {
            $ids[] = $this->makeEvent()->id;
        }

        $response = $this->getJson('/api/lan-relay/events?after=' . $ids[1] . '&limit=2', $this->apiHeaders())
            ->assertOk()
            ->assertJsonPath('latest_id', $ids[4]);

        $this->assertSame([$ids[2], $ids[3]], array_column($response->json('events'), 'id'));

        // limit 省略時は20件まで・昇順
        $response = $this->getJson('/api/lan-relay/events?after=0', $this->apiHeaders())->assertOk();
        $this->assertSame($ids, array_column($response->json('events'), 'id'));
    }

    public function test_default_limit_is_twenty(): void
    {
        for ($i = 0; $i < 25; $i++) {
            $this->makeEvent();
        }

        $response = $this->getJson('/api/lan-relay/events?after=0', $this->apiHeaders())->assertOk();
        $this->assertCount(20, $response->json('events'));
    }

    public function test_invalid_parameters_are_422_json(): void
    {
        $this->getJson('/api/lan-relay/events?after=0&limit=51', $this->apiHeaders())->assertStatus(422);
        $this->getJson('/api/lan-relay/events?after=0&limit=0', $this->apiHeaders())->assertStatus(422);
        $this->getJson('/api/lan-relay/events?after=-1', $this->apiHeaders())->assertStatus(422);
        $this->get('/api/lan-relay/events?after=abc', ['Authorization' => 'Bearer ' . self::TOKEN])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_event_fields_and_age_sec_from_server_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-30 10:00:00.250'));
        $event = $this->makeEvent([
            'type' => 'walk_in',
            'terminal' => 2,
            'camera' => 'cam1',
            'message' => 'ご来客（アポイントなし）A社 様 → 部署：営業部',
            'suppressed' => true,
            'suppress_reason' => 'terminal_disabled',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-10-30 10:00:12.650'));
        $item = $this->getJson('/api/lan-relay/events?after=0', $this->apiHeaders())->assertOk()->json('events.0');

        $this->assertSame([
            'id' => $event->id,
            'type' => 'walk_in',
            'terminal' => 2,
            'camera' => 'cam1',
            'message' => 'ご来客（アポイントなし）A社 様 → 部署：営業部',
            'occurred_at' => Carbon::parse('2026-10-30 10:00:00.250')->toIso8601String(),
            'age_sec' => 12.4,
            'suppressed' => true,
            'suppress_reason' => 'terminal_disabled',
        ], $item);

        // 個人情報の列（会社名・担当者名）は API に出さない
        $this->assertArrayNotHasKey('company_name', $item);
        $this->assertArrayNotHasKey('staff_name', $item);
    }

    // ── ack ────────────────────────────────────────

    public function test_ack_is_saved_and_overwritten(): void
    {
        $event = $this->makeEvent();

        $this->postJson("/api/lan-relay/events/{$event->id}/ack", [
            'display' => 'fail',
            'chime' => 'ok',
            'detail' => 'display timeout',
        ], $this->apiHeaders())->assertOk()->assertJson(['ok' => true]);

        $event->refresh();
        $this->assertSame('fail', $event->ack_display);
        $this->assertSame('ok', $event->ack_chime);
        $this->assertSame('display timeout', $event->ack_detail);
        $this->assertNotNull($event->acked_at);

        $this->postJson("/api/lan-relay/events/{$event->id}/ack", [
            'display' => 'ok',
            'chime' => 'skip',
            'detail' => str_repeat('あ', 250),
        ], $this->apiHeaders())->assertOk();

        $event->refresh();
        $this->assertSame('ok', $event->ack_display);
        $this->assertSame('skip', $event->ack_chime);
        $this->assertSame(200, mb_strlen($event->ack_detail));
    }

    public function test_ack_validation_and_not_found(): void
    {
        $event = $this->makeEvent();

        $this->postJson("/api/lan-relay/events/{$event->id}/ack", ['display' => 'done', 'chime' => 'ok'], $this->apiHeaders())
            ->assertStatus(422);
        $this->postJson("/api/lan-relay/events/{$event->id}/ack", ['display' => 'ok'], $this->apiHeaders())
            ->assertStatus(422);
        $this->postJson('/api/lan-relay/events/99999/ack', ['display' => 'ok', 'chime' => 'ok'], $this->apiHeaders())
            ->assertStatus(404);
    }

    // ── 90日削除 ────────────────────────────────────

    public function test_prune_deletes_events_older_than_ninety_days(): void
    {
        Carbon::setTestNow('2026-10-30 03:30:00');
        $old = $this->makeEvent(['occurred_at' => now()->subDays(90)->subSecond()]);
        $kept = $this->makeEvent(['occurred_at' => now()->subDays(89)]);
        $boundary = $this->makeEvent(['occurred_at' => now()->subDays(90)]);

        $this->artisan('lan-relay:prune')->assertSuccessful();

        $this->assertNull(LanRelayEvent::find($old->id));
        $this->assertNotNull(LanRelayEvent::find($kept->id));
        $this->assertNotNull(LanRelayEvent::find($boundary->id));
    }

    public function test_prune_days_option(): void
    {
        $this->makeEvent(['occurred_at' => now()->subDays(10)]);

        $this->artisan('lan-relay:prune', ['--days' => 7])->assertSuccessful();

        $this->assertSame(0, LanRelayEvent::count());
    }

    public function test_prune_is_scheduled_daily(): void
    {
        app(\Illuminate\Contracts\Console\Kernel::class);
        $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
        $events = collect($schedule->events())->filter(fn ($e) => str_contains($e->command ?? '', 'lan-relay:prune'));

        $this->assertCount(1, $events);
        $this->assertSame('30 3 * * *', $events->first()->expression);
    }
}
