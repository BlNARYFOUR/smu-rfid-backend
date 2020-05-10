<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleOwners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_owners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('is_vip')->default(false);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('id_number')->nullable();
            $table->string('phone_number');
            $table->string('address');
            $table->string('picture');
            $table->unsignedTinyInteger('owner_type_id')->nullable();

            $table->foreign('owner_type_id')->references('id')->on('owner_types')->onDelete('set null');

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
        Schema::dropIfExists('vehicle_owners');
    }
}
