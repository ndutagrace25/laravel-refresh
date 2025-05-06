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
        Schema::table('live_centrals', function (Blueprint $table) {
            if (! Schema::hasColumn('live_centrals', 'caption')) {
                $table->string('caption')->nullable();
            }
           
        });

        Schema::table('upload_videos', function (Blueprint $table) {
            if (! Schema::hasColumn('upload_videos', 'caption')) {
                $table->string('caption')->nullable();
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
        Schema::table('live_centrals', function (Blueprint $table) {
            if (Schema::hasColumn('live_centrals', 'caption')) {
                $table->dropColumn('caption');
            }
            
        });

        Schema::table('upload_videos', function (Blueprint $table) {
            if (! Schema::hasColumn('upload_videos', 'caption')) {
                $table->string('caption')->nullable();
            }
            
        });

        
    }
};
