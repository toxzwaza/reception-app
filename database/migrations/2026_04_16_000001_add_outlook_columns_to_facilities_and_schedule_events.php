<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('akioka_db')->table('facilities', function (Blueprint $table) {
            $table->string('outlook_resource_email')->nullable()->after('name');
        });

        Schema::connection('akioka_db')->table('schedule_events', function (Blueprint $table) {
            $table->string('outlook_event_id')->nullable()->after('status');
            $table->index('outlook_event_id');
        });
    }

    public function down()
    {
        Schema::connection('akioka_db')->table('schedule_events', function (Blueprint $table) {
            $table->dropIndex(['outlook_event_id']);
            $table->dropColumn('outlook_event_id');
        });

        Schema::connection('akioka_db')->table('facilities', function (Blueprint $table) {
            $table->dropColumn('outlook_resource_email');
        });
    }
};
