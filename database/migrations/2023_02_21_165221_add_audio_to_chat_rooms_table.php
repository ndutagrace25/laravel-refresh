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
        Schema::table('chat_rooms', function (Blueprint $table) {
             if (! Schema::hasColumn('chat_rooms', 'audio')) {
                $table->string('audio')->after('views')->nullable();
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
        Schema::table('chat_rooms', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_rooms', 'audio')) {
                $table->dropColumn('audio');
            } 
        });
    }
};
