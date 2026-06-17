<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('user_schedules')) {
            return;
        }

        if (Schema::hasColumn('user_schedules', 'outlook_event_id')) {
            return;
        }

        // 参加者の予定をOutlook（Microsoft Graph）からリアルタイム同期する際の
        // 突き合わせキー。施設予定（schedule_events.outlook_event_id）と同じ役割。
        Schema::table('user_schedules', function (Blueprint $table) {
            $table->string('outlook_event_id')->nullable()->after('status');
            $table->index(['user_id', 'outlook_event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('user_schedules')) {
            return;
        }

        if (!Schema::hasColumn('user_schedules', 'outlook_event_id')) {
            return;
        }

        Schema::table('user_schedules', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'outlook_event_id']);
            $table->dropColumn('outlook_event_id');
        });
    }
};
