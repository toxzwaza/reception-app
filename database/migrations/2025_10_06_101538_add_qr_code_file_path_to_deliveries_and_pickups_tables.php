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
        // deliveriesテーブルにqr_code_file_pathカラムを追加
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('qr_code_file_path')->nullable()->after('qr_code_url');
        });
        
        // pickupsテーブルにqr_code_file_pathカラムを追加
        Schema::table('pickups', function (Blueprint $table) {
            $table->string('qr_code_file_path')->nullable()->after('qr_code_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // deliveriesテーブルからqr_code_file_pathカラムを削除
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('qr_code_file_path');
        });
        
        // pickupsテーブルからqr_code_file_pathカラムを削除
        Schema::table('pickups', function (Blueprint $table) {
            $table->dropColumn('qr_code_file_path');
        });
    }
};
