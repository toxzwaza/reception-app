<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * アポイント登録時に作成される施設予約を、アポイントと紐付ける。
     * チェックイン通知で「同席する参加メンバー」をメンションするために使用する。
     */
    public function up(): void
    {
        Schema::table('schedule_events', function (Blueprint $table) {
            $table->foreignId('appointment_id')
                ->nullable()
                ->after('facility_id')
                ->constrained('appointments')
                ->nullOnDelete();
            $table->index('appointment_id');
        });
    }

    public function down(): void
    {
        Schema::table('schedule_events', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropIndex(['appointment_id']);
            $table->dropColumn('appointment_id');
        });
    }
};
