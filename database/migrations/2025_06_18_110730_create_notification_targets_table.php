<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationTargetsTable extends Migration
{
    public function up()
    {
        Schema::create('notification_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id');
            $table->unsignedBigInteger('targetable_id'); // ID of Farmer or Dealer
            $table->string('targetable_type'); // Model type: App\Models\Farmer, etc.
            $table->timestamps();

            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_targets');
    }

}