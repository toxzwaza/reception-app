<?php

namespace Tests\Feature\LanRelay;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * 受付横断連携テストの共通土台。
 *
 * 既存のマイグレーションは akioka_db 接続や MySQL 専用の ALTER を含み空DBで流せないため、
 * SQLite のメモリDBへ切り替え、テストに必要な最小限の表だけを作る。
 * 本番・ローカルの実DB（akioka_db など）には一切接続しない。
 */
abstract class LanRelayTestCase extends TestCase
{
    protected const TOKEN = 'test-lan-relay-token';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.connections.lan_relay_testing' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
            'database.default' => 'lan_relay_testing',
            'lan_relay.token' => self::TOKEN,
            'services.akitalk_bridge.url' => 'https://bridge.test/notify',
            'services.akitalk_bridge.api_key' => 'test-key',
        ]);

        // 安全装置：メモリDB以外（akioka_db 等）に向いていたら即中止
        $connection = DB::connection();
        if ($connection->getDriverName() !== 'sqlite' || $connection->getDatabaseName() !== ':memory:') {
            $this->fail('LanRelay テストは SQLite メモリDB でのみ実行します');
        }

        $this->createMinimalSchema();
        (require base_path('database/migrations/2026_10_30_000001_create_reception_terminals_table.php'))->up();
        (require base_path('database/migrations/2026_10_30_000002_create_lan_relay_events_table.php'))->up();

        // Teams（AkiTalk Bridge）は外へ出さない
        Http::fake(['*' => Http::response(['ok' => true, 'sent' => 1], 200)]);

        foreach (['none', '1', '2', '3', '9'] as $key) {
            RateLimiter::clear('lan-relay:terminal:' . $key);
        }

        $this->withoutVite();
    }

    private function createMinimalSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->integer('del_flg')->default(0);
            $table->timestamps();
        });
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone_number')->nullable();
            $table->integer('display_order')->nullable();
            $table->timestamps();
        });
        Schema::create('staff_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('reception_number')->nullable();
            $table->string('company_name')->nullable();
            $table->string('visitor_name')->nullable();
            $table->string('visitor_email')->nullable();
            $table->string('visitor_phone')->nullable();
            $table->unsignedBigInteger('staff_member_id')->nullable();
            $table->date('visit_date')->nullable();
            $table->string('visit_time')->nullable();
            $table->text('purpose')->nullable();
            $table->text('customer_message')->nullable();
            $table->string('qr_code')->nullable();
            $table->boolean('is_checked_in')->default(false);
            $table->dateTime('checked_in_at')->nullable();
            $table->boolean('send_flg')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('visitor_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('business_card_image')->nullable();
            $table->unsignedBigInteger('staff_member_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('reception_number')->nullable();
            $table->string('visitor_type')->nullable();
            $table->integer('number_of_people')->nullable();
            $table->text('purpose')->nullable();
            $table->dateTime('check_in_time')->nullable();
            $table->dateTime('check_out_time')->nullable();
            $table->timestamps();
        });
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('outlook_resource_email')->nullable();
            $table->timestamps();
        });
        Schema::create('schedule_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_id')->nullable();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->date('date')->nullable();
            $table->string('title')->nullable();
            $table->string('organizer_name')->nullable();
            $table->string('organizer_email')->nullable();
            $table->text('attendee_emails')->nullable();
            $table->string('start_datetime')->nullable();
            $table->string('end_datetime')->nullable();
            $table->string('badge')->nullable();
            $table->string('description_url')->nullable();
            $table->string('status')->nullable();
            $table->string('outlook_event_id')->nullable();
            $table->timestamps();
        });
        Schema::create('schedule_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_event_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('trigger_event');
            $table->boolean('is_active')->default(true);
            $table->text('settings')->nullable();
            $table->timestamps();
        });
        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_setting_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('notification_type');
            $table->string('notification_data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    protected function apiHeaders(?string $token = self::TOKEN): array
    {
        return $token === null ? ['Accept' => 'application/json'] : [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];
    }
}
