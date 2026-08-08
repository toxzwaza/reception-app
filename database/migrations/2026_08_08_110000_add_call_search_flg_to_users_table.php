<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 受付端末の担当者検索に表示するかどうかのフラグ。
     * 管理画面「担当者呼出管理」から切り替える。デフォルトは表示。
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('call_search_flg')->default(true)->after('mobile_phone')->comment('受付の担当者検索に表示するか');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('call_search_flg');
        });
    }
};
