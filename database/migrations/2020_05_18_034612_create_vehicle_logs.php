<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('action_in');
            $table->timestamp('date');
            $table->string('rfid_tag');
            $table->unsignedBigInteger('vehicle_id')->nullable();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_logs');
    }
}
