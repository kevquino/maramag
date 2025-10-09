<?php
// database/seeders/SangguniangBayanMemberSeeder.php

namespace Database\Seeders;

use App\Models\SangguniangBayanMember;
use Illuminate\Database\Seeder;

class SangguniangBayanMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            // Regular Members (8)
            [
                'name' => 'Hon. Juan Dela Cruz',
                'position' => 'Municipal Councilor',
                'position_type' => SangguniangBayanMember::POSITION_REGULAR,
                'bio' => 'Chairperson, Committee on Appropriations and Finance',
                'email' => 'juan.delacruz@municipality.gov.ph',
                'phone' => '+63 912 345 6789',
                'order' => 1,
                'committees' => ['Appropriations and Finance', 'Education', 'Health'],
                'district' => 'District 1',
                'term_start' => '2022',
                'term_end' => '2025',
            ],
            [
                'name' => 'Hon. Maria Santos',
                'position' => 'Municipal Councilor',
                'position_type' => SangguniangBayanMember::POSITION_REGULAR,
                'bio' => 'Chairperson, Committee on Education and Culture',
                'email' => 'maria.santos@municipality.gov.ph',
                'phone' => '+63 912 345 6790',
                'order' => 2,
                'committees' => ['Education and Culture', 'Women and Family'],
                'district' => 'District 2',
                'term_start' => '2022',
                'term_end' => '2025',
            ],
            [
                'name' => 'Hon. Pedro Reyes',
                'position' => 'Municipal Councilor',
                'position_type' => SangguniangBayanMember::POSITION_REGULAR,
                'bio' => 'Chairperson, Committee on Public Works and Infrastructure',
                'email' => 'pedro.reyes@municipality.gov.ph',
                'phone' => '+63 912 345 6791',
                'order' => 3,
                'committees' => ['Public Works and Infrastructure', 'Transportation'],
                'district' => 'District 3',
                'term_start' => '2022',
                'term_end' => '2025',
            ],
            [
                'name' => 'Hon. Ana Lim',
                'position' => 'Municipal Councilor',
                'position_type' => SangguniangBayanMember::POSITION_REGULAR,
                'bio' => 'Chairperson, Committee on Health and Sanitation',
                'email' => 'ana.lim@municipality.gov.ph',
                'phone' => '+63 912 345 6792',
                'order' => 4,
                'committees' => ['Health and Sanitation', 'Social Services'],
                'district' => 'District 4',
                'term_start' => '2022',
                'term_end' => '2025',
            ],
            [
                'name' => 'Hon. Roberto Garcia',
                'position' => 'Municipal Councilor',
                'position_type' => SangguniangBayanMember::POSITION_REGULAR,
                'bio' => 'Chairperson, Committee on Agriculture and Food Security',
                'email' => 'roberto.garcia@municipality.gov.ph',
                'phone' => '+63 912 345 6793',
                'order' => 5,
                'committees' => ['Agriculture and Food Security', 'Environmental Protection'],
                'district' => 'District 5',
                'term_start' => '2022',
                'term_end' => '2025',
            ],
            [
                'name' => 'Hon. Sofia Tan',
                'position' => 'Municipal Councilor',
                'position_type' => SangguniangBayanMember::POSITION_REGULAR,
                'bio' => 'Chairperson, Committee on Tourism and Cultural Affairs',
                'email' => 'sofia.tan@municipality.gov.ph',
                'phone' => '+63 912 345 6794',
                'order' => 6,
                'committees' => ['Tourism and Cultural Affairs', 'Youth and Sports'],
                'district' => 'District 6',
                'term_start' => '2022',
                'term_end' => '2025',
            ],
            [
                'name' => 'Hon. Miguel Torres',
                'position' => 'Municipal Councilor',
                'position_type' => SangguniangBayanMember::POSITION_REGULAR,
                'bio' => 'Chairperson, Committee on Peace and Order',
                'email' => 'miguel.torres@municipality.gov.ph',
                'phone' => '+63 912 345 6795',
                'order' => 7,
                'committees' => ['Peace and Order', 'Public Safety'],
                'district' => 'District 7',
                'term_start' => '2022',
                'term_end' => '2025',
            ],
            [
                'name' => 'Hon. Elena Mendoza',
                'position' => 'Municipal Councilor',
                'position_type' => SangguniangBayanMember::POSITION_REGULAR,
                'bio' => 'Chairperson, Committee on Market and Trade',
                'email' => 'elena.mendoza@municipality.gov.ph',
                'phone' => '+63 912 345 6796',
                'order' => 8,
                'committees' => ['Market and Trade', 'Cooperatives'],
                'district' => 'District 8',
                'term_start' => '2022',
                'term_end' => '2025',
            ],

            // Special Positions
            [
                'name' => 'Hon. Mark Anthony Villanueva',
                'position' => 'Sangguniang Kabataan Federation President',
                'position_type' => SangguniangBayanMember::POSITION_SK_PRESIDENT,
                'bio' => 'Representing the youth sector in the Sangguniang Bayan',
                'email' => 'sk.president@municipality.gov.ph',
                'phone' => '+63 912 345 6797',
                'order' => 9,
                'committees' => ['Youth and Sports Development', 'Education'],
                'district' => 'Municipal-wide',
                'term_start' => '2023',
                'term_end' => '2026',
            ],
            [
                'name' => 'Hon. Ricardo Morales',
                'position' => 'Liga ng mga Barangay President',
                'position_type' => SangguniangBayanMember::POSITION_LIGA_PRESIDENT,
                'bio' => 'Representing the barangays in the Sangguniang Bayan',
                'email' => 'liga.president@municipality.gov.ph',
                'phone' => '+63 912 345 6798',
                'order' => 10,
                'committees' => ['Barangay Affairs', 'Local Development'],
                'district' => 'Municipal-wide',
                'term_start' => '2023',
                'term_end' => '2026',
            ],
            [
                'name' => 'Hon. Datu Kalinga Lumawig',
                'position' => 'IP Mandatory Representative',
                'position_type' => SangguniangBayanMember::POSITION_IP_REPRESENTATIVE,
                'bio' => 'Representing the Indigenous Peoples in the Sangguniang Bayan',
                'email' => 'ip.representative@municipality.gov.ph',
                'phone' => '+63 912 345 6799',
                'order' => 11,
                'committees' => ['Indigenous Peoples Affairs', 'Cultural Preservation'],
                'district' => 'IP Communities',
                'term_start' => '2023',
                'term_end' => '2026',
            ],
        ];

        foreach ($members as $member) {
            SangguniangBayanMember::create($member);
        }
    }
}