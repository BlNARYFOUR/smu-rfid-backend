<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('model');
            $table->string('plate_number')->unique();
            $table->string('or_number');
            $table->string('cr_number');
            $table->string('licence_number');
            $table->string('rfid_tag')->nullable()->unique();
            $table->timestamp('activated_at')->nullable();
            $table->unsignedTinyInteger('vehicle_type_id')->nullable();
            $table->unsignedBigInteger('vehicle_owner_id');

            $table->foreign('vehicle_type_id')->references('id')->on('vehicle_types')->onDelete('set null');
            $table->foreign('vehicle_owner_id')->references('id')->on('vehicle_owners')->onDelete('cascade');

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
        Schema::dropIfExists('vehicles');
    }
}
