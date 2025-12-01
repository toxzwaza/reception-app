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
        Schema::connection('akioka_db')->table('visitors', function (Blueprint $table) {
            // 既存の外部キー制約を削除
            $table->dropForeign(['staff_member_id']);
            
            // 新しい外部キー制約をusersテーブルに追加
            $table->foreign('staff_member_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('akioka_db')->table('visitors', function (Blueprint $table) {
            // 外部キー制約を削除
            $table->dropForeign(['staff_member_id']);
            
            // 元のstaff_membersテーブルへの外部キー制約を復元
            $table->foreign('staff_member_id')->references('id')->on('staff_members');
        });
    }
};
