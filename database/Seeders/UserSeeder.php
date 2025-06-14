<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Find the 'admin' role ID
        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole) {
            // Check if admin user already exists
            $adminUser = User::where('username', 'admin')->first();

            if (!$adminUser) {
                // Create the admin user
                User::create([
                    'name' => 'Admin User',
                    'username' => 'admin',
                    'email' => 'admin@nbe.com.eg',
                    'password' => Hash::make('password'), // Use a strong password in production
                    'role_id' => $adminRole->id,
                    'status' => 'active',
                ]);
            }
        }
    }
}

