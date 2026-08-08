<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 受付画面の担当者ひらがな検索呼出用。
     * name_kana はカタカナで保存（検索時にひらがなを正規化して照合）。
     * mobile_phone は個人携帯番号（未登録者は部署電話のみ発信可）。
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name_kana', 100)->nullable()->after('name')->comment('ヨミ（カタカナ）');
            $table->string('mobile_phone', 30)->nullable()->after('email')->comment('個人携帯番号');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['name_kana', 'mobile_phone']);
        });
    }
};
