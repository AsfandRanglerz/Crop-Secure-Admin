<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVillageIdAndUcIdToCropInsurancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crop_insurances', function (Blueprint $table) {
            $table->unsignedBigInteger('village_id')->nullable()->after('village');
            $table->unsignedBigInteger('uc_id')->nullable()->after('tehsil_id');
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
            $table->dropColumn(['village_id', 'uc_id']);
        });
    }
}
