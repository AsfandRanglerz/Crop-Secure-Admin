<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimeAndLossFieldsToVillageWeatherHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('village_weather_histories', function (Blueprint $table) {
            $table->time('time')->nullable()->after('date');
            $table->boolean('loss_flag')->default(false)->after('rainfall');
            $table->string('loss_reason')->nullable()->after('loss_flag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('village_weather_histories', function (Blueprint $table) {
            $table->dropColumn(['time', 'loss_flag', 'loss_reason']);
        });
    }
}
