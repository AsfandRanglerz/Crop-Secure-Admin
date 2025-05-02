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
        
            // Farmer reference
            $table->unsignedBigInteger('user_id')->nullable();
        
            // Crop and area info
            $table->string('crop')->nullable(); // or use 'crop_id' if referencing crops
            $table->string('area_unit')->nullable();
            $table->float('area')->nullable();
        
            // Insurance info
            $table->unsignedBigInteger('insurance_type')->nullable();
            $table->unsignedBigInteger('company')->nullable();
        
            // Yield/benchmark logic
            $table->string('benchmark')->nullable(); // e.g. "90%"
            $table->float('benchmark_percent')->nullable(); // e.g. 90
            $table->float('sum_insured_100_percent')->nullable(); // per acre
            $table->float('sum_insured')->nullable(); // total
            $table->float('premium_price')->nullable();
        
            // Location info
            $table->unsignedBigInteger('district_id')->nullable();  // updated
            $table->unsignedBigInteger('tehsil_id')->nullable();
            $table->integer('year')->nullable();
        
            // Compensation
            $table->float('compensation')->nullable();
            $table->enum('status', ['loss', 'no loss'])->nullable();
        
            $table->timestamps();
        
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('farmers')->onDelete('cascade');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
            $table->foreign('tehsil_id')->references('id')->on('tehsils')->onDelete('set null');
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
