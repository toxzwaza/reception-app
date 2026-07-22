<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 受付端末の設置場所ごとに表示する導線（機能）の組み合わせを「画面パターン」として登録する。
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('screen_patterns')) {
            Schema::create('screen_patterns', function (Blueprint $table) {
                $table->id();
                $table->string('name');                 // パターン名（例: 本社受付 / 工場入口）
                $table->string('description')->nullable(); // 補足説明
                $table->json('features')->nullable();   // 有効な導線キーの配列
                $table->unsignedInteger('sort_order')->default(0); // 選択リストの表示順
                $table->boolean('is_active')->default(true); // 選択肢に表示するか
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('screen_patterns');
    }
};
