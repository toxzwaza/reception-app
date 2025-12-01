<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('akioka_db')->create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_setting_id')->constrained('notification_settings')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->comment('ユーザーID（akioka_db.usersテーブル参照）');
            $table->enum('notification_type', ['phone', 'email', 'teams']); // 通知方法
            $table->string('notification_data'); // 通知用データ（電話番号・メールアドレス・TeamsメンションID）
            $table->boolean('is_active')->default(true); // 有効/無効
            $table->timestamps();
            
            // インデックス
            $table->index(['notification_setting_id', 'is_active']);
            $table->index(['user_id', 'notification_type']);
        });
    }

    public function down(): void
    {
        Schema::connection('akioka_db')->dropIfExists('notification_recipients');
    }
};