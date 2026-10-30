<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 来客イベント（中継役が取得してディスプレイ表示・チャイムを行う）と、
     * 面接の2分抑止を端末単位で直列化するためのロック行。
     * 訪問者の個人名は保存しない。面接は会社名・氏名とも保存しない。
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lan_relay_events', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);                             // appointment / walk_in / interview
            $table->unsignedTinyInteger('terminal')->nullable();    // 端末番号（未設定・不正は null）
            $table->string('camera', 32)->nullable();               // 全画面にするカメラ src（記録時に決定）
            $table->string('company_name')->nullable();             // 表示用の会社名
            $table->string('staff_name')->nullable();               // 表示用の担当者名（アポなしは部署名）
            $table->string('message', 100);                         // ディスプレイに重ねる文字（60文字以内）
            $table->dateTime('occurred_at', 3);                     // 発生時刻（VPS時刻）
            $table->boolean('suppressed')->default(false);
            $table->string('suppress_reason', 32)->nullable();      // interview_cooldown / terminal_disabled / rate_limited
            $table->string('ack_display', 8)->nullable();           // ok / fail / skip
            $table->string('ack_chime', 8)->nullable();             // ok / fail / skip
            $table->string('ack_detail', 200)->nullable();
            $table->dateTime('acked_at')->nullable();               // 中継役が受け取った時刻
            $table->timestamps();

            $table->index('occurred_at');
        });

        Schema::create('lan_relay_cooldowns', function (Blueprint $table) {
            $table->string('terminal_key', 8)->primary();           // 端末番号 or 'none'（番号なし）
            $table->dateTime('last_fired_at', 3)->nullable();       // 最後に抑止しなかった面接の時刻
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lan_relay_cooldowns');
        Schema::dropIfExists('lan_relay_events');
    }
};
