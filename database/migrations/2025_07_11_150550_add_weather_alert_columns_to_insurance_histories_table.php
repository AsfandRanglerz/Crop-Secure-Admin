<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWeatherAlertColumnsToInsuranceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('insurance_histories', function (Blueprint $table) {
            $table->date('last_temperature_alert_sent_at')->nullable()->after('remaining_amount');
            $table->date('last_rainfall_alert_sent_at')->nullable()->after('last_temperature_alert_sent_at');
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
            $table->dropColumn('last_temperature_alert_sent_at');
            $table->dropColumn('last_rainfall_alert_sent_at');
        });
    }
}
