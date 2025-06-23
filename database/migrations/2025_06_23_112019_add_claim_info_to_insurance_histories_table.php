<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClaimInfoToInsuranceHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('insurance_histories', function (Blueprint $table) {
             $table->string('bank_holder_name')->nullable();
            $table->string('account_name')->nullable(); // You missed this in your last code
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->decimal('compensation_amount', 12, 2)->nullable();
            $table->decimal('claimed_amount', 12, 2)->nullable();
            $table->decimal('remaining_amount', 12, 2)->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->boolean('is_claim_seen')->default(0);
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
            $table->dropColumn([
                'bank_holder_name',
                'account_name',
                'account_number',
                'claimed_at',
            ]);
        });
    }
}
