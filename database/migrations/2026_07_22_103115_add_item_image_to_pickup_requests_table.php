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
        Schema::table('pickup_requests', function (Blueprint $table) {
            // 物品画像の保存パス（storage/app/public 配下）
            if (! Schema::hasColumn('pickup_requests', 'item_image')) {
                $table->string('item_image')->nullable()->after('item');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            if (Schema::hasColumn('pickup_requests', 'item_image')) {
                $table->dropColumn('item_image');
            }
        });
    }
};
