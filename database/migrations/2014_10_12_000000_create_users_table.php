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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('emp_no', 6)->unique()->comment('社員No');
            $table->string('name', 255)->comment('氏名');
            $table->text('password')->comment('パスワード');
            $table->string('email', 255)->nullable()->comment('メールアドレス');
            $table->tinyInteger('gender_flg')->default(0)->comment('性別 0:男性 1:女性');
            $table->unsignedBigInteger('group_id')->nullable()->comment('グループID');
            $table->unsignedBigInteger('position_id')->nullable()->comment('役職ID');
            $table->unsignedBigInteger('process_id')->nullable()->comment('プロセスID');
            $table->tinyInteger('is_admin')->default(0)->comment('管理者フラグ');
            $table->tinyInteger('dispatch_flg')->default(0)->comment('派遣フラグ');
            $table->tinyInteger('part_flg')->default(0)->comment('パートフラグ');
            $table->tinyInteger('always_order_flg')->default(0)->comment('常時発注フラグ');
            $table->tinyInteger('duty_flg')->default(0)->comment('当直フラグ');
            $table->string('fax_folder_name', 255)->nullable()->comment('FAXフォルダ名');
            $table->tinyInteger('del_flg')->default(0)->comment('削除フラグ');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
