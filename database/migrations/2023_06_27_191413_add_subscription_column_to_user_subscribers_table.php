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
        Schema::table('user_subscribers', function (Blueprint $table) {
            if (! Schema::hasColumn('user_subscribers', 'subscription_id')) {
                $table->string('subscription_id')->after('subscriber_id')->nullable();
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
        Schema::table('user_subscribers', function (Blueprint $table) {
            if (Schema::hasColumn('user_subscribers', 'subscription_id')) {
                $table->dropColumn('subscription_id')->nullable();
            }
        });
    }
};
