<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCropInsurancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crop_insurances', function (Blueprint $table) {
            $table->id();
            $table->string('crop')->nullable();
            $table->string('area_unit')->nullable();
            $table->string('area')->nullable();
            $table->string('insurance_type')->nullable();
            $table->string('company')->nullable();
            $table->string('benchmark')->nullable();
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
        Schema::dropIfExists('crop_insurances');
    }
}
