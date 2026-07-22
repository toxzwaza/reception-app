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
        Schema::create('pickup_requests', function (Blueprint $table) {
            $table->id();
            $table->string('requester_name');            // 依頼者
            $table->string('item');                       // 物品
            $table->string('storage_location')->nullable(); // 置き場所
            $table->string('contact_phone')->nullable();  // 問い合わせ電話番号
            $table->text('memo')->nullable();             // 備考
            $table->string('status')->default('pending'); // pending:未集荷 / completed:集荷済み
            $table->unsignedBigInteger('pickup_id')->nullable(); // 集荷実施時に紐づく pickups.id
            $table->timestamp('completed_at')->nullable(); // 集荷完了日時
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
        Schema::dropIfExists('pickup_requests');
    }
};
