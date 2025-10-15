<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks if needed
        // \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $this->call([
            UserSeeder::class,
        ]);
        
        // Then call other seeders that depend on users
        $this->call([
            NewsSeeder::class,
            BidsAwardsSeeder::class,
            FullDisclosureSeeder::class,
            TourismPackageSeeder::class,
            AwardsRecognitionSeeder::class,
            SangguniangBayanMemberSeeder::class,
            OrdinanceResolutionSeeder::class,
        ]);
        
        // Re-enable foreign key checks
        // \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}