<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Check if UserSeeder exists, if not create users in NewsSeeder
        $this->call([
            NewsSeeder::class,
        ]);
    }
}