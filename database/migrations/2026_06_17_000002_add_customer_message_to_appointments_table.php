<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('appointments')) {
            return;
        }

        if (Schema::hasColumn('appointments', 'customer_message')) {
            return;
        }

        // 受付完了画面で来訪者に表示する「お客様宛てメッセージ」
        Schema::table('appointments', function (Blueprint $table) {
            $table->text('customer_message')
                ->nullable()
                ->after('purpose')
                ->comment('お客様宛てメッセージ（受付完了画面に表示）');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('appointments')) {
            return;
        }

        if (!Schema::hasColumn('appointments', 'customer_message')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('customer_message');
        });
    }
};
