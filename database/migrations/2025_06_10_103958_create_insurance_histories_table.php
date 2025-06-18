<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInsuranceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('insurance_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('crop');
            $table->string('area_unit');
            $table->decimal('area', 8, 2);
            $table->string('insurance_type');
            $table->string('district');
            $table->string('tehsil');
            $table->string('company');
            $table->string('farmer_name');
            $table->decimal('premium_price', 10, 2);
            $table->decimal('sum_insured', 10, 2);
            $table->decimal('payable_amount', 10, 2);
            $table->decimal('benchmark', 10, 2)->nullable();
            $table->decimal('benchmark_price', 10, 2)->nullable();
            $table->string('land')->nullable();
            $table->string('receipt_number')->nullable();
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
        Schema::dropIfExists('insurance_histories');
    }
}
