<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 既存のテーブルがある場合はスキップ
        if (Schema::connection('akioka_db')->hasTable('user_schedules')) {
            return;
        }
        
        // akioka_dbに接続してテーブルを作成
        Schema::connection('akioka_db')->create('user_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->string('title', 500);
            $table->string('start_datetime', 10);
            $table->string('end_datetime', 10);
            $table->string('badge', 100)->nullable();
            $table->text('description_url')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            
            // インデックス
            $table->index(['user_id', 'date']);
            $table->index('date');
        });
        
        // TEXT型のカラムを含むユニーク制約は手動で作成（既に存在する場合はスキップ）
        try {
            DB::connection('akioka_db')->statement('ALTER TABLE user_schedules ADD UNIQUE KEY unique_user_event (user_id, date, description_url(255))');
        } catch (\Exception $e) {
            // 既にユニーク制約が存在する場合はスキップ
            if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('akioka_db')->dropIfExists('user_schedules');
    }
};
