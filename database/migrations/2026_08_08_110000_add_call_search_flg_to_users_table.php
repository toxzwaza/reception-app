<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 受付端末の担当者検索に表示するかどうかのフラグ。
     * 管理画面「担当者呼出管理」から切り替える。
     * 初期値はメールアドレスが登録されているユーザーのみ表示。
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('call_search_flg')->default(true)->after('mobile_phone')->comment('受付の担当者検索に表示するか');
        });

        // デフォルトの検索対象はメールアドレス登録済みユーザーのみ
        DB::table('users')
            ->where(fn ($query) => $query->whereNull('email')->orWhere('email', ''))
            ->update(['call_search_flg' => false]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('call_search_flg');
        });
    }
};
