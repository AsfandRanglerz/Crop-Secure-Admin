<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInsuranceSubTypeSatelliteNDVISTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('insurance_sub_type_satellite_n_d_v_i_s', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('insurance_type_id')->nullable();
            $table->date('date');
            $table->double('b8', 20, 4); 
            $table->double('b4', 20, 4);
            $table->double('ndvi', 20, 4);
            $table->timestamps();

            $table->foreign('insurance_type_id')->references('id')->on('insurance_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('insurance_sub_type_satellite_n_d_v_i_s');
    }
}
