<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 施設スケジュール（Outlook同期）の主催者・参加者メールを保持する。
     * 受付「アポイントありの方」当日一覧のタップ通知先に使用する。
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_events', function (Blueprint $table) {
            if (! Schema::hasColumn('schedule_events', 'organizer_name')) {
                $table->string('organizer_name')->nullable()->after('title');
            }
            if (! Schema::hasColumn('schedule_events', 'organizer_email')) {
                $table->string('organizer_email')->nullable()->after('organizer_name');
            }
            if (! Schema::hasColumn('schedule_events', 'attendee_emails')) {
                $table->json('attendee_emails')->nullable()->after('organizer_email');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_events', function (Blueprint $table) {
            foreach (['organizer_name', 'organizer_email', 'attendee_emails'] as $col) {
                if (Schema::hasColumn('schedule_events', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
