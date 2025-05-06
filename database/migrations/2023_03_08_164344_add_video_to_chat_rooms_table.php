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
        Schema::table('chat_room_comments', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_rooms', 'video_comment')) {
               $table->string('video_comment')->nullable();
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
        Schema::table('chat_room_comments', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_rooms', 'video_comment')) {
                $table->dropColumn('video_comment');
            } 
        });
    }
};
