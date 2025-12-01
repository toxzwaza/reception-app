<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('akioka_db')->create('staff_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('department');
            $table->string('teams_id')->nullable(); // Microsoft Teams ID
            $table->string('electronic_seal')->nullable(); // 電子印鑑のパス
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('akioka_db')->dropIfExists('staff_members');
    }
};