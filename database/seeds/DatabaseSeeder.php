<?php

use App\Models\User;
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
        // $this->call(UsersTableSeeder::class);
        VehicleType::create(array('name' => 'car'));
        VehicleType::create(array('name' => 'motorcycle'));
        VehicleType::create(array('name' => 'bus'));

        User::create(['admin' => true, 'email' => 'admin@smu.test', 'first_name' => 'admin', 'last_name' => 'admin', 'password' => 'password', 'email_verified_at' => now(),]);
        User::create(['email' => 'user@smu.test', 'first_name' => 'user', 'last_name' => 'user', 'password' => 'password','email_verified_at' => now(),]);

        factory(User::class, 30)->create();
    }
}
