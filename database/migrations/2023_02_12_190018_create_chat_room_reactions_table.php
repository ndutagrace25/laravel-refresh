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
        if (!Schema::hasTable('chat_room_reactions')) {
            Schema::create('chat_room_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chat_room_comment_id')->constrained('chat_room_comments');
                $table->string('reaction');
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
        Schema::dropIfExists('chat_room_reactions');
    }
};
