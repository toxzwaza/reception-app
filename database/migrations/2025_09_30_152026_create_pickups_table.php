<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickups', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('slip_image'); // 撮影した集荷伝票の画像パス
            $table->string('sealed_slip_image'); // 電子印を付与した画像のパス
            $table->string('qr_code_url'); // 生成したQRコードのURL
            $table->foreignId('staff_member_id')->constrained('staff_members'); // 電子印を提供したスタッフ
            $table->timestamp('picked_up_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickups');
    }
};