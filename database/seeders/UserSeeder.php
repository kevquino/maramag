<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Administrator
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@municipality.gov',
            'phone' => '+639123456789',
            'position' => 'IT Administrator',
            'role' => 'admin',
            'office' => 'Municipal Mayor\'s Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
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
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Municipal Mayor's Office
        User::create([
            'name' => 'Mayor\'s Office Admin',
            'email' => 'mayor.admin@municipality.gov',
            'phone' => '+639123456780',
            'position' => 'Executive Assistant',
            'role' => 'admin',
            'office' => 'Municipal Mayor\'s Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'news',
                'awards_recognition',
                'activity_logs'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Mayor\'s Office Staff',
            'email' => 'mayor.staff@municipality.gov',
            'phone' => '+639123456781',
            'position' => 'Administrative Aide',
            'role' => 'user',
            'office' => 'Municipal Mayor\'s Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'news'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Vice Mayor's Office
        User::create([
            'name' => 'Vice Mayor\'s Office Admin',
            'email' => 'vice.mayor.admin@municipality.gov',
            'phone' => '+639123456782',
            'position' => 'Legislative Staff Head',
            'role' => 'admin',
            'office' => 'Vice Mayor\'s Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'sangguniang_bayan',
                'ordinance_resolutions'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Vice Mayor\'s Office Staff',
            'email' => 'vice.mayor.staff@municipality.gov',
            'phone' => '+639123456783',
            'position' => 'Legislative Assistant',
            'role' => 'user',
            'office' => 'Vice Mayor\'s Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'sangguniang_bayan'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Office of the Secretary to the Sangguniang Bayan
        User::create([
            'name' => 'Sangguniang Bayan Secretary Admin',
            'email' => 'sb.secretary.admin@municipality.gov',
            'phone' => '+639123456784',
            'position' => 'Secretary to the Sanggunian',
            'role' => 'admin',
            'office' => 'Office of the Secretary to the Sangguniang Bayan',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'sangguniang_bayan',
                'ordinance_resolutions'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Sangguniang Bayan Secretary Staff',
            'email' => 'sb.secretary.staff@municipality.gov',
            'phone' => '+639123456785',
            'position' => 'Records Officer',
            'role' => 'user',
            'office' => 'Office of the Secretary to the Sangguniang Bayan',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'sangguniang_bayan'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Municipal Administrator's Office
        User::create([
            'name' => 'Municipal Administrator Admin',
            'email' => 'municipal.admin@municipality.gov',
            'phone' => '+639123456786',
            'position' => 'Municipal Administrator',
            'role' => 'admin',
            'office' => 'Municipal Administrator\'s Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'news',
                'activity_logs'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Municipal Administrator Staff',
            'email' => 'municipal.staff@municipality.gov',
            'phone' => '+639123456787',
            'position' => 'Administrative Officer',
            'role' => 'user',
            'office' => 'Municipal Administrator\'s Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'news'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Municipal Planning and Development Office
        User::create([
            'name' => 'MPDO Admin',
            'email' => 'mpdo.admin@municipality.gov',
            'phone' => '+639123456788',
            'position' => 'Planning Officer',
            'role' => 'admin',
            'office' => 'Municipal Planning and Development Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'full_disclosure'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'MPDO Staff',
            'email' => 'mpdo.staff@municipality.gov',
            'phone' => '+639123456789',
            'position' => 'Planning Assistant',
            'role' => 'user',
            'office' => 'Municipal Planning and Development Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'full_disclosure'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Municipal Engineer's Office
        User::create([
            'name' => 'Engineer\'s Office Admin',
            'email' => 'engineer.admin@municipality.gov',
            'phone' => '+639123456790',
            'position' => 'Municipal Engineer',
            'role' => 'admin',
            'office' => 'Municipal Engineer\'s Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'bids_awards'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Engineer\'s Office Staff',
            'email' => 'engineer.staff@municipality.gov',
            'phone' => '+639123456791',
            'position' => 'Engineering Assistant',
            'role' => 'user',
            'office' => 'Municipal Engineer\'s Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'bids_awards'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Office of the Building Official
        User::create([
            'name' => 'Building Official Admin',
            'email' => 'building.admin@municipality.gov',
            'phone' => '+639123456792',
            'position' => 'Building Official',
            'role' => 'admin',
            'office' => 'Office of the Building Official',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'business_permit'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Building Official Staff',
            'email' => 'building.staff@municipality.gov',
            'phone' => '+639123456793',
            'position' => 'Building Inspector',
            'role' => 'user',
            'office' => 'Office of the Building Official',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'business_permit'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // General Services Office
        User::create([
            'name' => 'GSO Admin',
            'email' => 'gso.admin@municipality.gov',
            'phone' => '+639123456794',
            'position' => 'General Services Officer',
            'role' => 'admin',
            'office' => 'General Services Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'bids_awards'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'GSO Staff',
            'email' => 'gso.staff@municipality.gov',
            'phone' => '+639123456795',
            'position' => 'Supply Officer',
            'role' => 'user',
            'office' => 'General Services Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'bids_awards'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Human Resource Management Office
        User::create([
            'name' => 'HRMO Admin',
            'email' => 'hrmo.admin@municipality.gov',
            'phone' => '+639123456796',
            'position' => 'HR Manager',
            'role' => 'admin',
            'office' => 'Human Resource Management Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'activity_logs'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'HRMO Staff',
            'email' => 'hrmo.staff@municipality.gov',
            'phone' => '+639123456797',
            'position' => 'HR Assistant',
            'role' => 'user',
            'office' => 'Human Resource Management Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Continue with other offices following the same pattern...
        // [Rest of the offices would follow the same structure with appropriate positions]

        // PIO Officer (Special Role)
        User::create([
            'name' => 'PIO Officer',
            'email' => 'pio.officer@municipality.gov',
            'phone' => '+639123456798',
            'position' => 'Public Information Officer',
            'role' => 'PIO Officer',
            'office' => 'Public Information Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'news' // Only news permission - dashboard will be hidden
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // PIO Staff
        User::create([
            'name' => 'PIO Staff',
            'email' => 'pio.staff@municipality.gov',
            'phone' => '+639123456799',
            'position' => 'Information Officer',
            'role' => 'PIO Staff',
            'office' => 'Public Information Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard',
                'news'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Create Regular User (Limited Access)
        User::create([
            'name' => 'Regular User',
            'email' => 'user@municipality.gov',
            'phone' => '+639123456800',
            'position' => 'Clerk',
            'role' => 'user',
            'office' => 'Other',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => [
                'dashboard'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Create Inactive User
        User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@municipality.gov',
            'phone' => '+639123456801',
            'position' => 'Former Employee',
            'role' => 'user',
            'office' => 'Other',
            'is_active' => false,
            'avatar' => null,
            'last_login_at' => now()->subMonths(3),
            'last_login_ip' => '192.168.1.100',
            'login_count' => 15,
            'permissions' => [
                'dashboard'
            ],
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now()->subMonths(6),
            'password' => Hash::make('password123'),
        ]);
    }
}