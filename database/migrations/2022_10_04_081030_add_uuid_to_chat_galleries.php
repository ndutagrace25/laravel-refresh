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
            if (! Schema::hasColumn('chat_galleries', 'uuid')) {
                $table->string('uuid');
            }   
        });
        Schema::table('chat_gallery_files', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_galleries_files', 'uuid')) {
                $table->string('uuid');
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
            if(Schema::hasColumn('chat_galleries', 'uuid')){
                $table->dropColumn('uuid');
            }
        });
        Schema::table('chat_gallery_files', function (Blueprint $table) {
            if(Schema::hasColumn('chat_galleries_files', 'uuid')){
                $table->dropColumn('uuid');
            }
        });
        
    }
};
