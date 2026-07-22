<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('groups', function (Blueprint $table) {
            // 受付画面・部署電話番号管理での表示順。未設定は末尾扱い（既定は id 順を踏襲）。
            if (! Schema::hasColumn('groups', 'display_order')) {
                $table->integer('display_order')->nullable()->after('phone_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('groups', function (Blueprint $table) {
            if (Schema::hasColumn('groups', 'display_order')) {
                $table->dropColumn('display_order');
            }
        });
    }
};
