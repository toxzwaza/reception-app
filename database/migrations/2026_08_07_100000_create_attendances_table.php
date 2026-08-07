<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 出退勤記録テーブル。
     * 1ユーザー1日1レコード（出勤で作成、退勤で clock_out_at を更新）。
     * users テーブルへの外部キーは既存方針（visitors 等）に合わせて張らない。
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('社員ID（users.id）');
            $table->date('work_date')->comment('勤務日');
            $table->dateTime('clock_in_at')->comment('出勤日時');
            $table->dateTime('clock_out_at')->nullable()->comment('退勤日時');
            $table->timestamps();

            $table->unique(['user_id', 'work_date']);
            $table->index('work_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
