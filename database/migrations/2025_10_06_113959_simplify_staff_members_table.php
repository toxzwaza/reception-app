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
        Schema::table('staff_members', function (Blueprint $table) {
            // 不要なカラムを削除
            $table->dropColumn(['name', 'email', 'department', 'teams_id', 'teams_webhook_url', 'electronic_seal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_members', function (Blueprint $table) {
            $table->string('name');
            $table->string('email')->unique();
            $table->string('department');
            $table->string('teams_id')->nullable();
            $table->string('teams_webhook_url')->nullable();
            $table->string('electronic_seal')->nullable();
        });
    }
};