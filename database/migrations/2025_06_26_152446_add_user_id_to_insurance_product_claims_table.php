<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToInsuranceProductClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('insurance_product_claims', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('insurance_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('insurance_product_claims', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
}
