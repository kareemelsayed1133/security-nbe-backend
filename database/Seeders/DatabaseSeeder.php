<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        // Call the seeders in the correct order
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            // You can add other seeders here later, for example:
            // BranchSeeder::class,
            // DeviceTypeSeeder::class,
        ]);
    }
}

