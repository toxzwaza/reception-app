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
        Schema::connection('akioka_db')->create('schedule_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->onDelete('cascade');
            $table->date('date');
            $table->string('title', 500);
            $table->string('start_datetime', 10);
            $table->string('end_datetime', 10);
            $table->string('badge', 100)->nullable();
            $table->text('description_url')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            
            // インデックス
            $table->index(['facility_id', 'date']);
            $table->index('date');
        });
        
        // TEXT型のカラムを含むユニーク制約は手動で作成
        DB::connection('akioka_db')->statement('ALTER TABLE schedule_events ADD UNIQUE KEY unique_event (facility_id, date, description_url(255))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('akioka_db')->dropIfExists('schedule_events');
    }
};

