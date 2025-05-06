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
        Schema::table('chat_galleries', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_galleries', 'tag_id')) {
                $table->foreignId('tag_id')->constrained('tags')->nullable();
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
        Schema::table('chat_galleries', function (Blueprint $table) {
            //
        });
    }
};
