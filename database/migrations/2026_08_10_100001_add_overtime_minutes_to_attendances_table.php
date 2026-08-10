<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 残業時間（分）。退勤打刻時に勤務時間マスタの終業時刻を基準に計算して保存する。
     * 打刻時点の定時で確定させるため、集計はこのカラムを合算するだけでよい。
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedInteger('overtime_minutes')->default(0)->after('clock_out_at')->comment('残業時間（分）');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('overtime_minutes');
        });
    }
};
