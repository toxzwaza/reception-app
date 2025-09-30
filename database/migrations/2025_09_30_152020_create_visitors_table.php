<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('visitor_name');
            $table->string('phone')->nullable();
            $table->string('business_card_image')->nullable();
            $table->foreignId('staff_member_id')->constrained('staff_members');
            $table->string('qr_code')->nullable();
            $table->timestamp('check_in_time');
            $table->timestamp('check_out_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};