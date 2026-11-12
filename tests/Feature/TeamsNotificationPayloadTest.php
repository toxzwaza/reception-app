<?php

namespace Tests\Feature;

use App\Services\TeamsNotificationService;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * AkiTalk Bridge へ送る payload にシステム名・アイコン・色が入ることの確認。
 * 実際の送信はしない（Http::fake）。DB も使わない。
 */
class TeamsNotificationPayloadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.akitalk_bridge.url' => 'https://bridge.test/notify',
            'services.akitalk_bridge.api_key' => 'test-key',
            'services.akitalk_bridge.sender' => 'AK受付システム通知',
            'services.akitalk_bridge.system' => '受付システム',
            'services.akitalk_bridge.icon' => '🏢',
            'services.akitalk_bridge.color' => 'good',
        ]);

        Http::fake(['*' => Http::response(['ok' => true, 'sent' => 1], 200)]);
    }

    public function test_payload_にシステム名・アイコン・色が入る(): void
    {
        $ok = app(TeamsNotificationService::class)->notify(['a@example.com'], '件名', '本文');

        $this->assertTrue($ok);
        Http::assertSent(function (HttpRequest $request) {
            $data = $request->data();
            return $request->url() === 'https://bridge.test/notify'
                && $request->hasHeader('x-api-key', 'test-key')
                && $data['system'] === '受付システム'
                && $data['icon'] === '🏢'
                && $data['color'] === 'good'
                && $data['from'] === 'AK受付システム通知'
                && $data['title'] === '🚨【緊急】件名'
                && $data['recipients'] === ['a@example.com'];
        });
    }

    public function test_通常通知にも同じ3項目が入る(): void
    {
        app(TeamsNotificationService::class)->notify('a@example.com', '出勤', '本文', null, false);

        Http::assertSent(fn (HttpRequest $request) => $request->data()['system'] === '受付システム'
            && $request->data()['title'] === '出勤');
    }

    public function test_設定を空にすると3項目を送らない(): void
    {
        config([
            'services.akitalk_bridge.system' => '',
            'services.akitalk_bridge.icon' => null,
            'services.akitalk_bridge.color' => '',
        ]);

        app(TeamsNotificationService::class)->notify(['a@example.com'], '件名', '本文');

        Http::assertSent(function (HttpRequest $request) {
            $data = $request->data();
            return !array_key_exists('system', $data)
                && !array_key_exists('icon', $data)
                && !array_key_exists('color', $data);
        });
    }

    public function test_宛先が空なら送信しない(): void
    {
        $this->assertTrue(app(TeamsNotificationService::class)->notify([], '件名', '本文'));
        Http::assertNothingSent();
    }
}
