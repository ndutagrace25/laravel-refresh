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
        Schema::table('live_schedule_galleries', function (Blueprint $table) {
            if (! Schema::hasColumn('live_schedule_galleries', 'is_premium')) {
                $table->string('is_premium')->after('title')->nullable();
            } 
            if (! Schema::hasColumn('live_schedule_galleries', 'start_time')) {
                $table->string('start_time')->after('title')->nullable();
            } 
            if (! Schema::hasColumn('live_schedule_galleries', 'end_time')) {
                $table->string('end_time')->after('title')->nullable();
            } 
           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('live_schedule_galleries', function (Blueprint $table) {
            if (! Schema::hasColumn('live_schedule_galleries', 'is_premium')) {
                 $table->dropColumn('is_premium');
            } 
            if (! Schema::hasColumn('live_schedule_galleries', 'start_time')) {
                $table->dropColumn('start_time');
            } 
            if (! Schema::hasColumn('live_schedule_galleries', 'end_time')) {
                $table->dropColumn('end_time');
            } 
        });
    }
};
