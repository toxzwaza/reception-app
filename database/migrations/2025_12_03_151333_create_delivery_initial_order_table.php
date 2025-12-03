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
        Schema::create('delivery_initial_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->onDelete('cascade')->comment('納品ID');
            $table->unsignedBigInteger('initial_order_id')->comment('発注ID');
            $table->timestamps();
            
            // インデックス
            $table->index('delivery_id');
            $table->index('initial_order_id');
            $table->unique(['delivery_id', 'initial_order_id'], 'delivery_initial_order_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_initial_order');
    }
};
