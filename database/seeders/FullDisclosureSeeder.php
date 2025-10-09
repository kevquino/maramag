<?php
// database/seeders/FullDisclosureSeeder.php

namespace Database\Seeders;

use App\Models\FullDisclosure;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class FullDisclosureSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();

        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ]);
        }

        $documents = [
            // Approved Budget
            [
                'title' => 'Approved Budget 2025',
                'category' => FullDisclosure::CATEGORY_APPROVED_BUDGET,
                'description' => 'Annual approved budget for fiscal year 2025',
                'file_name' => 'approved_budget_2025.pdf',
            ],
            [
                'title' => 'Approved Budget 2024',
                'category' => FullDisclosure::CATEGORY_APPROVED_BUDGET,
                'description' => 'Annual approved budget for fiscal year 2024',
                'file_name' => 'approved_budget_2024.pdf',
            ],
            [
                'title' => 'Approved Budget 2023',
                'category' => FullDisclosure::CATEGORY_APPROVED_BUDGET,
                'description' => 'Annual approved budget for fiscal year 2023',
                'file_name' => 'approved_budget_2023.pdf',
            ],

            // Annual Procurement Plan
            [
                'title' => 'Annual Procurement Plan 2025',
                'category' => FullDisclosure::CATEGORY_PROCUREMENT_PLAN,
                'description' => 'Comprehensive procurement plan for 2025',
                'file_name' => 'procurement_plan_2025.pdf',
            ],
            [
                'title' => 'Annual Procurement Plan 2024',
                'category' => FullDisclosure::CATEGORY_PROCUREMENT_PLAN,
                'description' => 'Comprehensive procurement plan for 2024',
                'file_name' => 'procurement_plan_2024.pdf',
            ],

            // Gender and Development
            [
                'title' => 'GAD Accomplishment Report 2024',
                'category' => FullDisclosure::CATEGORY_GENDER_DEVELOPMENT,
                'description' => 'Gender and Development accomplishment report for 2024',
                'file_name' => 'gad_report_2024.pdf',
            ],

            // Full Disclosure Policy
            [
                'title' => 'Full Disclosure Policy 2025',
                'category' => FullDisclosure::CATEGORY_FULL_DISCLOSURE,
                'description' => 'Full disclosure policy document for 2025',
                'file_name' => 'full_disclosure_2025.pdf',
            ],

            // Audit Report
            [
                'title' => 'Audit Report 2023',
                'category' => FullDisclosure::CATEGORY_AUDIT_REPORT,
                'description' => 'Annual audit report for fiscal year 2023',
                'file_name' => 'audit_report_2023.pdf',
            ],

            // Executive Summary
            [
                'title' => 'Executive Summary 2023',
                'category' => FullDisclosure::CATEGORY_EXECUTIVE_SUMMARY,
                'description' => 'Executive summary report for 2023',
                'file_name' => 'executive_summary_2023.pdf',
            ],
        ];

        foreach ($documents as $document) {
            FullDisclosure::create([
                'title' => $document['title'],
                'category' => $document['category'],
                'description' => $document['description'],
                'file_path' => 'full-disclosure/' . $document['file_name'],
                'file_name' => $document['file_name'],
                'file_size' => '2.5 MB',
                'file_type' => 'application/pdf',
                'user_id' => $user->id,
                'is_published' => true,
                'created_at' => now()->subDays(rand(1, 365)),
            ]);
        }
    }
}