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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('お知らせタイトル');
            $table->text('content')->comment('お知らせ内容');
            $table->string('type')->default('info')->comment('種別（info, warning, error）');
            $table->date('start_date')->comment('表示開始日');
            $table->date('end_date')->comment('表示終了日');
            $table->boolean('is_active')->default(true)->comment('有効フラグ');
            $table->integer('display_order')->default(0)->comment('表示順');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('announcements');
    }
};
