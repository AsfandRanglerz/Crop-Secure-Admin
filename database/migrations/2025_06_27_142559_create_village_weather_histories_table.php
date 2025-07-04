<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVillageWeatherHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('village_weather_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->float('temperature')->nullable();
            $table->float('rainfall')->nullable();
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
        Schema::dropIfExists('village_weather_histories');
    }
}
