<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 勤務時間マスタ。
     * 出退勤の残業計算で参照する定時（始業・終業）を管理する。
     * 定時変更時はレコードを更新すれば以後の残業計算に反映される。
     */
    public function up(): void
    {
        Schema::create('work_time_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('設定名');
            $table->time('work_start_time')->comment('始業時刻');
            $table->time('work_end_time')->comment('終業時刻（これ以降を残業として計算）');
            $table->timestamps();
        });

        DB::table('work_time_settings')->insert([
            'name' => '標準勤務',
            'work_start_time' => '08:00:00',
            'work_end_time' => '17:10:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('work_time_settings');
    }
};
