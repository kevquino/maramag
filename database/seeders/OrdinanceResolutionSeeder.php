<?php
// database/seeders/OrdinanceResolutionSeeder.php

namespace Database\Seeders;

use App\Models\OrdinanceResolution;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OrdinanceResolutionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        
        $ordinanceResolutions = [
            [
                'title' => 'An Ordinance Regulating Business Operations Within the Municipality',
                'number' => '2024-001',
                'type' => 'ordinance',
                'description' => 'An ordinance regulating the operation of businesses, prescribing fees, and providing penalties for violations.',
                'date_approved' => '2024-01-15',
                'date_effectivity' => '2024-02-01',
                'sponsor' => 'Hon. Juan Dela Cruz',
                'co_sponsors' => ['Hon. Maria Santos', 'Hon. Pedro Reyes'],
                'status' => 'active',
                'categories' => ['business', 'revenue'],
                'is_featured' => true,
                'is_active' => true,
                'user_id' => $user->id,
            ],
            [
                'title' => 'A Resolution Approving the Annual Budget for Fiscal Year 2024',
                'number' => '2024-002',
                'type' => 'resolution',
                'description' => 'A resolution approving the annual budget and appropriating funds for municipal operations.',
                'date_approved' => '2024-01-20',
                'date_effectivity' => '2024-01-21',
                'sponsor' => 'Hon. Maria Santos',
                'co_sponsors' => ['Hon. Juan Dela Cruz', 'Hon. Pedro Reyes'],
                'status' => 'active',
                'categories' => ['appropriation'],
                'is_featured' => false,
                'is_active' => true,
                'user_id' => $user->id,
            ],
            [
                'title' => 'An Ordinance Establishing Environmental Protection Zones',
                'number' => '2024-003',
                'type' => 'ordinance',
                'description' => 'An ordinance establishing protected environmental zones and regulating activities within these areas.',
                'date_approved' => '2024-02-10',
                'date_effectivity' => '2024-03-01',
                'sponsor' => 'Hon. Pedro Reyes',
                'co_sponsors' => ['Hon. Maria Santos'],
                'status' => 'active',
                'categories' => ['environment', 'zoning'],
                'is_featured' => true,
                'is_active' => true,
                'user_id' => $user->id,
            ],
        ];

        foreach ($ordinanceResolutions as $ordinanceResolution) {
            OrdinanceResolution::create($ordinanceResolution);
        }
    }
}