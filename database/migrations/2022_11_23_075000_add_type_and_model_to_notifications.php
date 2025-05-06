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
        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'action')) {
                $table->string('action')->nullable();
            } 
            if (! Schema::hasColumn('notifications', 'user')) {
                $table->integer('user')->nullable();
            } 
            if (! Schema::hasColumn('notifications', 'subject')) {
                $table->integer('subject')->nullable();
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
        Schema::table('notifications', function (Blueprint $table) {
            if(Schema::hasColumn('notifications', 'action')){
                $table->dropColumn('action');
            }
            if(Schema::hasColumn('notifications', 'user')){
                $table->dropColumn('user');
            }
            if(Schema::hasColumn('notifications', 'subject')){
                $table->dropColumn('subject');
            }
        });
    }
};
