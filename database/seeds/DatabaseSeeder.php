<?php

use App\Models\OwnerType;
use App\Models\User;
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

        VehicleOwner::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'id_number' => '0123456789',
            'phone_number' => '09 (XXX) XX XX XX',
            'address' => 'Saint Mary\'s University, Bayombong, 3700',
            'picture' => 'images/vehicle_owners/owner.jpg',
            'owner_type_id' => 2,
        ]);

        VehicleType::create(array('name' => 'car'));
        VehicleType::create(array('name' => 'motorcycle'));
        VehicleType::create(array('name' => 'bus'));

        User::create(['admin' => true, 'email' => 'admin@smu.test', 'first_name' => 'admin', 'last_name' => 'admin', 'password' => 'password', 'email_verified_at' => now(),]);
        User::create(['email' => 'user@smu.test', 'first_name' => 'user', 'last_name' => 'user', 'password' => 'password','email_verified_at' => now(),]);

        factory(User::class, 30)->create();
    }
}
