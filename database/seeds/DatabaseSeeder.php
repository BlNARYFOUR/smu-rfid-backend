<?php

use App\Models\OwnerType;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleOwner;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        OwnerType::create(array('name' => 'visitor'));
        OwnerType::create(array('name' => 'faculty'));
        OwnerType::create(array('name' => 'student'));

        VehicleOwner::create(['first_name' => 'John', 'last_name' => 'Doe', 'id_number' => '0123456789', 'phone_number' => '09 (XXX) XX XX XX', 'address' => 'Saint Mary\'s University, Bayombong, 3700', 'picture' => 'images/vehicle_owners/owner.jpg', 'owner_type_id' => 2,]);

        VehicleType::create(array('name' => 'car'));
        VehicleType::create(array('name' => 'motorcycle'));
        VehicleType::create(array('name' => 'bus'));

        Vehicle::create(['vehicle_type_id' => 1, 'model' => 'Toyota', 'plate_number' => 'ABC123', 'or_number' => '123456', 'cr_number' => '7654321', 'licence_number' => '1234567', 'rfid_tag' => 'TEST901239TEST932109TEST', 'vehicle_owner_id' => 1, 'activated_at' => now(),]);
        Vehicle::create(['vehicle_type_id' => 2, 'model' => 'Kawasaki Ninja H2R', 'plate_number' => 'XYZ890', 'or_number' => '654321', 'cr_number' => '1234567', 'licence_number' => '7654321', 'rfid_tag' => 'FOST321234FOST432123FOST', 'vehicle_owner_id' => 1, 'activated_at' => now()->subYears(1)->subMonth(),]);

        User::create(['admin' => true, 'email' => 'admin@smu.test', 'first_name' => 'admin', 'last_name' => 'admin', 'password' => 'password', 'email_verified_at' => now(),]);
        User::create(['email' => 'user@smu.test', 'first_name' => 'user', 'last_name' => 'user', 'password' => 'password','email_verified_at' => now(),]);

        factory(User::class, 30)->create();
    }
}
