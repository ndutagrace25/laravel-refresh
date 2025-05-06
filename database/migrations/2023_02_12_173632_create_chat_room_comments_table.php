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
        if (!Schema::hasTable('chat_room_comments')) {
            Schema::create('chat_room_comments', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id')->unsigned();
                $table->integer('parent_id')->unsigned()->nullable();
                $table->text('comment');
                $table->string('audio_comment')->nullable();
                $table->integer('commentable_id')->unsigned()->nullable();
                $table->string('commentable_type')->nullable();
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
        Schema::dropIfExists('chat_room_comments');
    }
};
