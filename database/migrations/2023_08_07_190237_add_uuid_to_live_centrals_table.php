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
            if (! Schema::hasColumn('live_centrals', 'uuid')) {
                $table->string('uuid')->after('id')->nullable();
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
            if (Schema::hasColumn('live_centrals', 'uuid')) {
                $table->dropColumn('uuid');
            }
        });
    }
};
