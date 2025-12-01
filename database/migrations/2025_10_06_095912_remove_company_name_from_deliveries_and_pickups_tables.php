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
        // deliveriesテーブルからcompany_nameカラムを削除
        Schema::connection('akioka_db')->table('deliveries', function (Blueprint $table) {
            $table->dropColumn('company_name');
        });
        
        // pickupsテーブルからcompany_nameカラムを削除
        Schema::connection('akioka_db')->table('pickups', function (Blueprint $table) {
            $table->dropColumn('company_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // deliveriesテーブルにcompany_nameカラムを復元
        Schema::connection('akioka_db')->table('deliveries', function (Blueprint $table) {
            $table->string('company_name')->after('id');
        });
        
        // pickupsテーブルにcompany_nameカラムを復元
        Schema::connection('akioka_db')->table('pickups', function (Blueprint $table) {
            $table->string('company_name')->after('id');
        });
    }
};
