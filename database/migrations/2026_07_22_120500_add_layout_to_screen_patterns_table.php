<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 受付トップ上での各導線カードの配置（位置・サイズ）を保持する。
     * 12列グリッド上の {i, x, y, w, h} 配列を JSON で格納する。
     *
     * @return void
     */
    public function up()
    {
        Schema::table('screen_patterns', function (Blueprint $table) {
            if (! Schema::hasColumn('screen_patterns', 'layout')) {
                $table->json('layout')->nullable()->after('features');
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
        Schema::table('screen_patterns', function (Blueprint $table) {
            if (Schema::hasColumn('screen_patterns', 'layout')) {
                $table->dropColumn('layout');
            }
        });
    }
};
