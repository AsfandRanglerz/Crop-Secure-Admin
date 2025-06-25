<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInsuranceProductClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('insurance_product_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_id')->constrained('insurance_histories');
            $table->foreignId('dealer_id')->constrained('authorized_dealers');
            $table->foreignId('item_id')->constrained('items');
            $table->decimal('price', 10, 2);
            $table->string('receiver_name')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('delivery_status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('insurance_product_claims');
    }
}
