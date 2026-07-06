<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // QR に埋め込むアクセス用トークン（ID直打ちでの他書類閲覧を防止）
            $table->string('token', 64)->nullable()->after('qr_code_url')->index();
        });

        // 既存レコードにもトークンを付与（未設定分のみ）
        DB::table('deliveries')->whereNull('token')->pluck('id')->each(function ($id) {
            DB::table('deliveries')->where('id', $id)->update(['token' => Str::random(40)]);
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropIndex(['token']);
            $table->dropColumn('token');
        });
    }
};
