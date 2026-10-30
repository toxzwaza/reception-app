<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 受付タブレットの端末番号と、来客時に全画面にするカメラの紐付け。
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reception_terminals', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('number')->unique(); // 端末番号 1〜99
            $table->string('name', 100);                     // 設置場所名
            $table->string('camera_src', 32);                // 全画面にするカメラ（config/lan_relay.php の許可リスト）
            $table->boolean('enabled')->default(true);       // 無効ならチャイム・全画面を鳴らさない
            $table->timestamps();
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reception_terminals');
    }
};
