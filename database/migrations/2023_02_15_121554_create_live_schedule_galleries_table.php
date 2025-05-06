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
        if (!Schema::hasTable('live_schedule_galleries')) {
            Schema::create('live_schedule_galleries', function (Blueprint $table) {
                $table->id();
                $table->float('entry_fee')->nullable();
                $table->foreignId('user_id')->constrained('users');
                $table->foreignId('channel_id')->constrained('channels');
                $table->foreignId('tag_id')->constrained('tags');
                $table->string('title');
                $table->string('description')->nullable();
                $table->dateTime('date');
                $table->string('timezone')->nullable();
                $table->integer('pre-live')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('live_schedule_galleries');
    }
};
