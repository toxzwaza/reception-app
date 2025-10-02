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
        Schema::create('interview_phones', function (Blueprint $table) {
            $table->id();
            $table->string('department_name')->comment('部署名');
            $table->string('contact_person')->comment('担当者名');
            $table->string('phone_number')->comment('電話番号');
            $table->string('extension_number')->nullable()->comment('内線番号');
            $table->text('notes')->nullable()->comment('備考');
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
        Schema::dropIfExists('interview_phones');
    }
};
