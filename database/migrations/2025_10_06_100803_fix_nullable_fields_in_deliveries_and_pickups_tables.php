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
        Schema::connection('akioka_db')->table('deliveries', function (Blueprint $table) {
            $table->dropColumn('qr_code_url');
        });
        
        Schema::connection('akioka_db')->table('deliveries', function (Blueprint $table) {
            $table->string('qr_code_url')->nullable()->after('sealed_document_image');
        });
        
        Schema::connection('akioka_db')->table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['staff_member_id']);
            $table->dropColumn('staff_member_id');
        });
        
        Schema::connection('akioka_db')->table('deliveries', function (Blueprint $table) {
            $table->foreignId('staff_member_id')->nullable()->constrained('staff_members')->after('qr_code_url');
        });
        
        // pickupsテーブルのフィールドをnullableに変更
        Schema::connection('akioka_db')->table('pickups', function (Blueprint $table) {
            $table->dropColumn('qr_code_url');
        });
        
        Schema::connection('akioka_db')->table('pickups', function (Blueprint $table) {
            $table->string('qr_code_url')->nullable()->after('sealed_slip_image');
        });
        
        Schema::connection('akioka_db')->table('pickups', function (Blueprint $table) {
            $table->dropForeign(['staff_member_id']);
            $table->dropColumn('staff_member_id');
        });
        
        Schema::connection('akioka_db')->table('pickups', function (Blueprint $table) {
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
        Schema::connection('akioka_db')->table('deliveries', function (Blueprint $table) {
            $table->dropColumn('qr_code_url');
        });
        
        Schema::connection('akioka_db')->table('deliveries', function (Blueprint $table) {
            $table->string('qr_code_url')->after('sealed_document_image');
        });
        
        Schema::connection('akioka_db')->table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['staff_member_id']);
            $table->dropColumn('staff_member_id');
        });
        
        Schema::connection('akioka_db')->table('deliveries', function (Blueprint $table) {
            $table->foreignId('staff_member_id')->constrained('staff_members')->after('qr_code_url');
        });
        
        // pickupsテーブルのフィールドをnot nullに戻す
        Schema::connection('akioka_db')->table('pickups', function (Blueprint $table) {
            $table->dropColumn('qr_code_url');
        });
        
        Schema::connection('akioka_db')->table('pickups', function (Blueprint $table) {
            $table->string('qr_code_url')->after('sealed_slip_image');
        });
        
        Schema::connection('akioka_db')->table('pickups', function (Blueprint $table) {
            $table->dropForeign(['staff_member_id']);
            $table->dropColumn('staff_member_id');
        });
        
        Schema::connection('akioka_db')->table('pickups', function (Blueprint $table) {
            $table->foreignId('staff_member_id')->constrained('staff_members')->after('qr_code_url');
        });
    }
};
