<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@municipality.gov',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'office' => 'Mayor\'s Office',
            'is_active' => true,
            'permissions' => [
                'dashboard',
                'news',
                'bids_awards',
                'full_disclosure',
                'tourism',
                'awards_recognition',
                'sangguniang_bayan',
                'ordinance_resolutions',
                'user_management',
                'activity_logs',
                'trash',
                'business_permit'
            ],
            'email_verified_at' => now(),
        ]);

        // Create PIO Officer with only news permission
        User::create([
            'name' => 'PIO Officer',
            'email' => 'pio.officer@example.com',
            'password' => Hash::make('password123'),
            'role' => 'PIO Officer',
            'office' => 'Public Information Office',
            'is_active' => true,
            'permissions' => [
                'news' // Only news permission - dashboard will be hidden
            ],
            'email_verified_at' => now(),
        ]);

        // Create PIO Staff
        User::create([
            'name' => 'PIO Staff',
            'email' => 'pio.staff@municipality.gov',
            'password' => Hash::make('password123'),
            'role' => 'PIO Staff',
            'office' => 'Public Information Office',
            'is_active' => true,
            'permissions' => [
                'dashboard',
                'news'
            ],
            'email_verified_at' => now(),
        ]);

        // Create Tourism Officer
        User::create([
            'name' => 'Tourism Officer',
            'email' => 'tourism.officer@municipality.gov',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'office' => 'Tourism Office',
            'is_active' => true,
            'permissions' => [
                'dashboard',
                'tourism'
            ],
            'email_verified_at' => now(),
        ]);

        // Create Planning Officer (Full Disclosure)
        User::create([
            'name' => 'Planning Officer',
            'email' => 'planning.officer@municipality.gov',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'office' => 'Municipal Planning and Development Office',
            'is_active' => true,
            'permissions' => [
                'dashboard',
                'full_disclosure'
            ],
            'email_verified_at' => now(),
        ]);

        // Create Regular User (Limited Access)
        User::create([
            'name' => 'Regular User',
            'email' => 'user@municipality.gov',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'office' => 'Other',
            'is_active' => true,
            'permissions' => [
                'dashboard'
            ],
            'email_verified_at' => now(),
        ]);

        // Create Inactive User
        User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@municipality.gov',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'office' => 'Other',
            'is_active' => false,
            'permissions' => [
                'dashboard'
            ],
            'email_verified_at' => now(),
        ]);
    }
}