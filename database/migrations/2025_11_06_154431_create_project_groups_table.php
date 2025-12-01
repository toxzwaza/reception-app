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
        if (Schema::connection('akioka_db')->hasTable('project_groups')) {
            return;
        }
        
        Schema::connection('akioka_db')->create('project_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
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
        Schema::connection('akioka_db')->dropIfExists('project_groups');
    }
};
