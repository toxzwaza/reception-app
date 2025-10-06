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
        // deliveriesテーブルのsealed_document_imageカラムを削除して再作成
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('sealed_document_image');
        });
        
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('sealed_document_image')->nullable()->after('document_image');
        });
        
        // pickupsテーブルのsealed_slip_imageカラムを削除して再作成
        Schema::table('pickups', function (Blueprint $table) {
            $table->dropColumn('sealed_slip_image');
        });
        
        Schema::table('pickups', function (Blueprint $table) {
            $table->string('sealed_slip_image')->nullable()->after('slip_image');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // deliveriesテーブルのsealed_document_imageカラムを削除して再作成（not null）
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('sealed_document_image');
        });
        
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('sealed_document_image')->after('document_image');
        });
        
        // pickupsテーブルのsealed_slip_imageカラムを削除して再作成（not null）
        Schema::table('pickups', function (Blueprint $table) {
            $table->dropColumn('sealed_slip_image');
        });
        
        Schema::table('pickups', function (Blueprint $table) {
            $table->string('sealed_slip_image')->after('slip_image');
        });
    }
};
