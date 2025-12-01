<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('akioka_db')->create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 通知名
            $table->string('description')->nullable(); // 通知の説明
            $table->string('trigger_event'); // トリガーイベント（delivery_received, pickup_received, interview_call等）
            $table->boolean('is_active')->default(true); // 有効/無効
            $table->json('settings')->nullable(); // 追加設定（JSON形式）
            $table->timestamps();
            
            // インデックス
            $table->index(['trigger_event', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::connection('akioka_db')->dropIfExists('notification_settings');
    }
};