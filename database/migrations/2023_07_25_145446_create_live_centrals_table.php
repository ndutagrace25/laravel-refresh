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
        Schema::create('live_centrals', function (Blueprint $table) {
            $table->id();
            $table->string('live_id');
            $table->string('room_id');
            $table->integer('user_id');
            $table->integer('status');
            $table->string('username');
            $table->integer('channel_id')->nullable();
            $table->integer('tag_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('live_centrals');
    }
};
