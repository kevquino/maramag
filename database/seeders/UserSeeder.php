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
            'email' => 'superadmin@municipality.gov',
            'phone' => '+639123456789',
            'position' => 'IT Administrator',
            'role' => 'superadmin',
            'office' => 'Municipal Mayor\'s Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode([]), // Superadmin doesn't need permissions
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Public Information Office - Admin
        User::create([
            'name' => 'PIO Admin',
            'email' => 'pio.admin@municipality.gov',
            'phone' => '+639123456780',
            'position' => 'Public Information Officer',
            'role' => 'admin',
            'office' => 'Public Information Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode(['dashboard', 'news', 'trash']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Public Information Office - Staff
        User::create([
            'name' => 'PIO Staff',
            'email' => 'pio.staff@municipality.gov',
            'phone' => '+639123456781',
            'position' => 'Information Officer',
            'role' => 'staff',
            'office' => 'Public Information Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode(['dashboard', 'news', 'trash']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Bids and Awards Committee - Admin
        User::create([
            'name' => 'BAC Admin',
            'email' => 'bac.admin@municipality.gov',
            'phone' => '+639123456782',
            'position' => 'BAC Chairman',
            'role' => 'admin',
            'office' => 'Bids and Awards Committee',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode(['dashboard', 'bids_awards', 'trash']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Bids and Awards Committee - Staff
        User::create([
            'name' => 'BAC Staff',
            'email' => 'bac.staff@municipality.gov',
            'phone' => '+639123456783',
            'position' => 'BAC Secretary',
            'role' => 'staff',
            'office' => 'Bids and Awards Committee',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode(['dashboard', 'bids_awards', 'trash']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Municipal Planning and Development Office - Admin
        User::create([
            'name' => 'MPDO Admin',
            'email' => 'mpdo.admin@municipality.gov',
            'phone' => '+639123456784',
            'position' => 'Planning Officer',
            'role' => 'admin',
            'office' => 'Municipal Planning and Development Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode(['dashboard', 'full_disclosure', 'trash']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Municipal Planning and Development Office - Staff
        User::create([
            'name' => 'MPDO Staff',
            'email' => 'mpdo.staff@municipality.gov',
            'phone' => '+639123456785',
            'position' => 'Planning Assistant',
            'role' => 'staff',
            'office' => 'Municipal Planning and Development Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode(['dashboard', 'full_disclosure', 'trash']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Tourism Office - Admin
        User::create([
            'name' => 'Tourism Admin',
            'email' => 'tourism.admin@municipality.gov',
            'phone' => '+639123456786',
            'position' => 'Tourism Officer',
            'role' => 'admin',
            'office' => 'Tourism Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode(['dashboard', 'tourism', 'full_disclosure', 'trash']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Tourism Office - Staff
        User::create([
            'name' => 'Tourism Staff',
            'email' => 'tourism.staff@municipality.gov',
            'phone' => '+639123456787',
            'position' => 'Tourism Assistant',
            'role' => 'staff',
            'office' => 'Tourism Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode(['dashboard', 'tourism', 'full_disclosure', 'trash']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Mayor's Office - Admin
        User::create([
            'name' => 'Mayor\'s Office Admin',
            'email' => 'mayor.admin@municipality.gov',
            'phone' => '+639123456788',
            'position' => 'Executive Assistant',
            'role' => 'admin',
            'office' => 'Mayor\'s Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode(['dashboard', 'news', 'tourism', 'awards_recognition', 'trash']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Mayor's Office - Staff
        User::create([
            'name' => 'Mayor\'s Office Staff',
            'email' => 'mayor.staff@municipality.gov',
            'phone' => '+639123456789',
            'position' => 'Administrative Aide',
            'role' => 'staff',
            'office' => 'Mayor\'s Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode(['dashboard', 'news', 'tourism', 'awards_recognition', 'trash']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Business Permit and Licensing Office - Admin
        User::create([
            'name' => 'BPLO Admin',
            'email' => 'bplo.admin@municipality.gov',
            'phone' => '+639123456790',
            'position' => 'Business Permit Officer',
            'role' => 'admin',
            'office' => 'Business Permit and Licensing Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode(['dashboard', 'business_permit', 'new_application', 'renewal_permit']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Business Permit and Licensing Office - Staff
        User::create([
            'name' => 'BPLO Staff',
            'email' => 'bplo.staff@municipality.gov',
            'phone' => '+639123456791',
            'position' => 'Permit Processor',
            'role' => 'staff',
            'office' => 'Business Permit and Licensing Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode(['dashboard', 'business_permit', 'new_application', 'renewal_permit']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Vice Mayor's Office - Admin
        User::create([
            'name' => 'Vice Mayor Admin',
            'email' => 'vice.mayor.admin@municipality.gov',
            'phone' => '+639123456792',
            'position' => 'Legislative Staff Head',
            'role' => 'admin',
            'office' => 'Vice Mayor\'s Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode(['dashboard', 'sangguniang_bayan', 'ordinance_resolutions', 'trash']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Vice Mayor's Office - Staff
        User::create([
            'name' => 'Vice Mayor Staff',
            'email' => 'vice.mayor.staff@municipality.gov',
            'phone' => '+639123456793',
            'position' => 'Legislative Assistant',
            'role' => 'staff',
            'office' => 'Vice Mayor\'s Office',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode(['dashboard', 'sangguniang_bayan', 'ordinance_resolutions', 'trash']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Sangguniang Bayan - Admin
        User::create([
            'name' => 'SB Admin',
            'email' => 'sb.admin@municipality.gov',
            'phone' => '+639123456794',
            'position' => 'Secretary to the Sanggunian',
            'role' => 'admin',
            'office' => 'Sangguniang Bayan',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode(['dashboard', 'sangguniang_bayan', 'ordinance_resolutions', 'trash']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Sangguniang Bayan - Staff
        User::create([
            'name' => 'SB Staff',
            'email' => 'sb.staff@municipality.gov',
            'phone' => '+639123456795',
            'position' => 'Records Officer',
            'role' => 'staff',
            'office' => 'Sangguniang Bayan',
            'is_active' => true,
            'avatar' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'login_count' => 0,
            'permissions' => json_encode(['dashboard', 'sangguniang_bayan', 'ordinance_resolutions', 'trash']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Create Inactive User
        User::create([
            'name' => 'Inactive Staff',
            'email' => 'inactive.staff@municipality.gov',
            'phone' => '+639123456796',
            'position' => 'Former Employee',
            'role' => 'staff',
            'office' => 'Other',
            'is_active' => false,
            'avatar' => null,
            'last_login_at' => now()->subMonths(3),
            'last_login_ip' => '192.168.1.100',
            'login_count' => 15,
            'permissions' => json_encode(['dashboard']),
            'timezone' => 'Asia/Manila',
            'locale' => 'en',
            'email_verified_at' => now()->subMonths(6),
            'password' => Hash::make('password123'),
        ]);
    }
}