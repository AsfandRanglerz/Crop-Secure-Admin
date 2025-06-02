<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPremiumPriceToCompanyInsuranceTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_insurance_types', function (Blueprint $table) {
            $table->decimal('premium_price', 10, 2)->after('price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_insurance_types', function (Blueprint $table) {
            $table->decimal('premium_price', 10, 2)->nullable()->after('price');
        });
    }
}
