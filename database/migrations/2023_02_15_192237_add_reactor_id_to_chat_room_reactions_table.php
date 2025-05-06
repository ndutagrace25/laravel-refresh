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
        Schema::table('chat_room_reactions', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_room_reactions', 'reactor_id')) {
                $table->foreignId('reactor_id')->after('reaction')->constrained('users');
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
        Schema::table('chat_room_reactions', function (Blueprint $table) {
            $table->dropForeign(['reactor_id']);
            $table->dropColumn('reactor_id');
        });
    }
};
