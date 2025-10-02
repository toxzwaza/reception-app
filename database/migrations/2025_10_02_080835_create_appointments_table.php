<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('reception_number', 4)->unique()->comment('受付番号（4桁）');
            $table->string('company_name')->comment('会社名');
            $table->string('visitor_name')->comment('訪問者氏名');
            $table->string('visitor_email')->nullable()->comment('訪問者メールアドレス');
            $table->string('visitor_phone')->nullable()->comment('訪問者電話番号');
            $table->foreignId('staff_member_id')->constrained()->comment('担当スタッフID');
            $table->date('visit_date')->comment('訪問予定日');
            $table->time('visit_time')->comment('訪問予定時刻');
            $table->text('purpose')->nullable()->comment('訪問目的');
            $table->string('qr_code')->nullable()->comment('QRコードデータ');
            $table->boolean('is_checked_in')->default(false)->comment('チェックイン済みフラグ');
            $table->timestamp('checked_in_at')->nullable()->comment('チェックイン日時');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
