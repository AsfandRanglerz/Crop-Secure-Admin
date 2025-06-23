<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInsuranceTypeIdToInsuranceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('insurance_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('insurance_type_id')->nullable()->after('insurance_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('insurance_histories', function (Blueprint $table) {
            $table->dropColumn('insurance_type_id');
        });
    }
}
