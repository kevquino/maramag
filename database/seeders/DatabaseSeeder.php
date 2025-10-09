<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            NewsSeeder::class,
            BidsAwardsSeeder::class,
            FullDisclosureSeeder::class,
            TourismPackageSeeder::class,
            AwardsRecognitionSeeder::class, // Add this line
        ]);
    }
}