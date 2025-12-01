<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('akioka_db')->create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('delivery_type'); // 納品書 or 受領書
            $table->string('document_image'); // 撮影した書類の画像パス
            $table->string('sealed_document_image'); // 電子印を付与した画像のパス
            $table->string('qr_code_url'); // 生成したQRコードのURL
            $table->foreignId('staff_member_id')->constrained('staff_members'); // 電子印を提供したスタッフ
            $table->timestamp('received_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('akioka_db')->dropIfExists('deliveries');
    }
};