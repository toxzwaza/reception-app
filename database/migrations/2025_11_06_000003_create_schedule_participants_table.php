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
        Schema::connection('akioka_db')->create('schedule_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_event_id')->constrained('schedule_events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // 同じ予定に同じユーザーを重複登録しない
            $table->unique(['schedule_event_id', 'user_id']);
            
            // インデックス
            $table->index('schedule_event_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('akioka_db')->dropIfExists('schedule_participants');
    }
};

