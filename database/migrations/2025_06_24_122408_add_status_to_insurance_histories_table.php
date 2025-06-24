<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToInsuranceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('insurance_histories', function (Blueprint $table) {
            $table->string('status')->default('unclaimed')->after('claimed_at');
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
            $table->dropColumn('status');
        });
    }
}
