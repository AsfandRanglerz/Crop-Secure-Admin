<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFatherNameToAuthorizedDealersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('authorized_dealers', function (Blueprint $table) {
            $table->string('father_name')->nullable()->after('name');
            $table->string('dob')->nullable()->after('father_name');
            $table->string('district')->nullable()->after('dob');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('authorized_dealers', function (Blueprint $table) {
            //
        });
    }
}
