<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUcToCropInsurancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crop_insurances', function (Blueprint $table) {
            $table->string('uc')->nullable()->after('tehsil_id');
            $table->string('village')->nullable()->after('uc');
            $table->longtext('other')->nullable()->after('village');
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
            $table->dropColumn('uc');
            $table->dropColumn('village');
            $table->dropColumn('other');
        });
    }
}
