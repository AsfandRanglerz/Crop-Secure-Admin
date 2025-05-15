<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVillageLatitudeToCropInsurancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crop_insurances', function (Blueprint $table) {
            $table->string('village_latitude')->nullable()->after('village');
            $table->string('village_longitude')->nullable()->after('village_latitude');
            
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
            $table->dropColumn('village_latitude');
            $table->dropColumn('village_longitude');
        });
    }
}
