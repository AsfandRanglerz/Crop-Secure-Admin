<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubTypeIdToCropInsurancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crop_insurances', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_type_id')->nullable()->after('insurance_type');
            $table->foreign('sub_type_id')->references('id')->on('insurance_sub_types')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crop_insurances', function (Blueprint $table) {
            $table->dropForeign(['sub_type_id']);
            $table->dropColumn('sub_type_id');
        });
    }
}
