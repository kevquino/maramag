<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create PIO Officer
        User::create([
            'name' => 'PIO Officer',
            'email' => 'pio.officer@example.com',
            'password' => Hash::make('password'),
            'role' => 'PIO Officer',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create PIO Staff
        User::create([
            'name' => 'PIO Staff',
            'email' => 'pio.staff@example.com',
            'password' => Hash::make('password'),
            'role' => 'PIO Staff',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create regular user (no news access)
        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create additional test users if needed
        // User::factory(10)->create();
    }
}