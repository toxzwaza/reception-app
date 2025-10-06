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
        // deliveriesテーブルのフィールドをnullableに変更
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('qr_code_url');
        });
        
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('qr_code_url')->nullable()->after('sealed_document_image');
        });
        
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['staff_member_id']);
            $table->dropColumn('staff_member_id');
        });
        
        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreignId('staff_member_id')->nullable()->constrained('staff_members')->after('qr_code_url');
        });
        
        // pickupsテーブルのフィールドをnullableに変更
        Schema::table('pickups', function (Blueprint $table) {
            $table->dropColumn('qr_code_url');
        });
        
        Schema::table('pickups', function (Blueprint $table) {
            $table->string('qr_code_url')->nullable()->after('sealed_slip_image');
        });
        
        Schema::table('pickups', function (Blueprint $table) {
            $table->dropForeign(['staff_member_id']);
            $table->dropColumn('staff_member_id');
        });
        
        Schema::table('pickups', function (Blueprint $table) {
            $table->foreignId('staff_member_id')->nullable()->constrained('staff_members')->after('qr_code_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // deliveriesテーブルのフィールドをnot nullに戻す
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('qr_code_url');
        });
        
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('qr_code_url')->after('sealed_document_image');
        });
        
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['staff_member_id']);
            $table->dropColumn('staff_member_id');
        });
        
        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreignId('staff_member_id')->constrained('staff_members')->after('qr_code_url');
        });
        
        // pickupsテーブルのフィールドをnot nullに戻す
        Schema::table('pickups', function (Blueprint $table) {
            $table->dropColumn('qr_code_url');
        });
        
        Schema::table('pickups', function (Blueprint $table) {
            $table->string('qr_code_url')->after('sealed_slip_image');
        });
        
        Schema::table('pickups', function (Blueprint $table) {
            $table->dropForeign(['staff_member_id']);
            $table->dropColumn('staff_member_id');
        });
        
        Schema::table('pickups', function (Blueprint $table) {
            $table->foreignId('staff_member_id')->constrained('staff_members')->after('qr_code_url');
        });
    }
};
