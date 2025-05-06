<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Upload video for user as alternative to user going live
     * @return void
     */
    public function up()
    {
        Schema::create('upload_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained('channels');
            $table->foreignId('tag_id')->constrained('tags');
            $table->string('uuid');
            $table->string('location')->nullable();
            $table->boolean('age_restriction')->default(0);
            $table->integer('minimum_age')->nullable();
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
        Schema::dropIfExists('upload_videos');
    }
};
