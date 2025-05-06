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
            if (! Schema::hasColumn('live_schedule_galleries', 'url')) {
                $table->string('url')->after('pre-live')->default(''); 
            }
            if (! Schema::hasColumn('live_schedule_galleries', 'stream_key')) {
                $table->string('stream_key')->after('pre-live')->default(''); 
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
            if (! Schema::hasColumn('live_schedule_galleries', 'url')) {
                $table->dropColumn('url');
            }
            if (! Schema::hasColumn('live_schedule_galleries', 'stream_key')) {
                $table->dropColumn('stream_key');
            } 
        });
    }
};
