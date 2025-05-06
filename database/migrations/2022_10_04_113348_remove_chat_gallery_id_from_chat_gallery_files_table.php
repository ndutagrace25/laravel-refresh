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
        Schema::table('chat_gallery_files', function (Blueprint $table) {
            $table->dropForeign('chat_gallery_files_chat_gallery_id_foreign');
            $table->dropColumn('chat_gallery_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_gallery_files', function (Blueprint $table) {
            $table->integer('chat_gallery_id');
        });
    }
};
