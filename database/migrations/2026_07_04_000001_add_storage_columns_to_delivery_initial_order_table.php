<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 紐づけ時に在庫加算した「格納先」と「加算数量」を保持する。
     * 紐づけ解除時の在庫巻き戻しに使用する。
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_initial_order', function (Blueprint $table) {
            $table->unsignedBigInteger('storage_address_id')->nullable()->after('initial_order_id')->comment('在庫加算した格納先ID');
            $table->integer('added_quantity')->nullable()->after('storage_address_id')->comment('在庫に加算した数量');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_initial_order', function (Blueprint $table) {
            $table->dropColumn(['storage_address_id', 'added_quantity']);
        });
    }
};
