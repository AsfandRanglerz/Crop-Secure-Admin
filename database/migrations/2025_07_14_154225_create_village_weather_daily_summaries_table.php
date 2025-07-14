<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVillageWeatherDailySummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('village_weather_daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('village_id');
            $table->date('date');
            $table->float('avg_temperature');
            $table->float('avg_rainfall');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('village_weather_daily_summaries');
    }
}
