<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCropIdToInsuranceSubTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('insurance_sub_types', function (Blueprint $table) {
            $table->unsignedBigInteger('crop_name_id')->nullable();
    $table->foreign('crop_name_id')->references('id')->on('ensured_crop_name')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('insurance_sub_types', function (Blueprint $table) {
            //
        });
    }
}
