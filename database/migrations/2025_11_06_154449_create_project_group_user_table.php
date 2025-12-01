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
        // 既存のテーブルがある場合はスキップ
        if (Schema::connection('akioka_db')->hasTable('project_group_user')) {
            return;
        }
        
        Schema::connection('akioka_db')->create('project_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_group_id')->constrained('project_groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // ユニーク制約（同じユーザーを同じプロジェクトグループに重複して追加しない）
            $table->unique(['project_group_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('akioka_db')->dropIfExists('project_group_user');
    }
};
