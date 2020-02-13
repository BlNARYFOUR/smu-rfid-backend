<?php

use App\VehicleType;
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
        // $this->call(UsersTableSeeder::class);
        VehicleType::create(array('name' => 'car'));
        VehicleType::create(array('name' => 'motorcycle'));
        VehicleType::create(array('name' => 'bus'));
    }
}
