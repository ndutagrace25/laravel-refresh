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
        Schema::table('live_schedule_galleries', function (Blueprint $table) {
            if (! Schema::hasColumn('live_schedule_galleries', 'product_id')) {
                $table->string('product_id')->after('status')->nullable();
            }
            
        });
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'subscription_id')) {
                $table->string('subscription_id')->nullable();
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
        Schema::table('live_schedule_galleries', function (Blueprint $table) {
            if (Schema::hasColumn('live_schedule_galleries', 'product_id')) {
                $table->dropColumn('product_id')->nullable();
            }
        });
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'subscription_id')) {
                $table->dropColumn('subscription_id')->nullable();
            }
        });
    }
};
