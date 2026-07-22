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
            // 依頼者の所属部署（groups.id）。部署選択→担当者絞り込み・電話番号既定に使用
            if (! Schema::hasColumn('pickup_requests', 'requester_group_id')) {
                $table->unsignedBigInteger('requester_group_id')->nullable()->after('requester_name');
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
            if (Schema::hasColumn('pickup_requests', 'requester_group_id')) {
                $table->dropColumn('requester_group_id');
            }
        });
    }
};
