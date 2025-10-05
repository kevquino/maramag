<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create users with news access roles
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $pioOfficer = User::firstOrCreate(
            ['email' => 'pio.officer@example.com'],
            [
                'name' => 'PIO Officer',
                'password' => bcrypt('password'),
                'role' => 'PIO Officer',
                'email_verified_at' => now(),
            ]
        );

        $news = [
            [
                'title' => 'New Product Launch Exceeds Expectations',
                'slug' => 'new-product-launch-exceeds-expectations',
                'excerpt' => 'Our latest product has seen record sales in the first week of release, surpassing all projections.',
                'content' => $this->generateContent('New Product Launch'),
                'published_at' => Carbon::parse('2023-10-15'),
                'author_id' => $admin->id,
                'category' => 'Business',
                'status' => 'published',
                'is_featured' => true,
                'image_path' => null,
            ],
            [
                'title' => 'Company Announces Quarterly Results',
                'slug' => 'company-announces-quarterly-results',
                'excerpt' => 'Strong growth reported across all business segments with a 25% increase in revenue.',
                'content' => $this->generateContent('Quarterly Results'),
                'published_at' => Carbon::parse('2023-10-10'),
                'author_id' => $pioOfficer->id,
                'category' => 'Finance',
                'status' => 'published',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'Industry Conference Highlights Innovation',
                'slug' => 'industry-conference-highlights-innovation',
                'excerpt' => 'Our team presented groundbreaking research at the annual industry conference.',
                'content' => $this->generateContent('Industry Conference'),
                'published_at' => Carbon::parse('2023-10-05'),
                'author_id' => $admin->id,
                'category' => 'Events',
                'status' => 'draft',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'Partnership with Tech Giant Announced',
                'slug' => 'partnership-with-tech-giant-announced',
                'excerpt' => 'New strategic partnership set to revolutionize the industry with combined expertise.',
                'content' => $this->generateContent('Partnership Announcement'),
                'published_at' => Carbon::parse('2023-09-28'),
                'author_id' => $pioOfficer->id,
                'category' => 'Partnerships',
                'status' => 'published',
                'is_featured' => true,
                'image_path' => null,
            ],
            [
                'title' => 'Sustainability Initiative Reaches Milestone',
                'slug' => 'sustainability-initiative-reaches-milestone',
                'excerpt' => 'Company achieves carbon neutrality goal ahead of schedule.',
                'content' => $this->generateContent('Sustainability Milestone'),
                'published_at' => Carbon::parse('2023-09-20'),
                'author_id' => $admin->id,
                'category' => 'Sustainability',
                'status' => 'archived',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'New Office Opening in Downtown',
                'slug' => 'new-office-opening-in-downtown',
                'excerpt' => 'Expanding our presence with a new state-of-the-art office location.',
                'content' => $this->generateContent('New Office Opening'),
                'published_at' => Carbon::parse('2023-09-15'),
                'author_id' => $pioOfficer->id,
                'category' => 'Company News',
                'status' => 'published',
                'is_featured' => false,
                'image_path' => null,
            ],
        ];

        foreach ($news as $article) {
            News::create($article);
        }
    }

    private function generateContent(string $title): string
    {
        return "<h1>{$title}</h1>
                <p>This is a detailed article about {$title}. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
                <h2>Key Points</h2>
                <ul>
                    <li>First important point about {$title}</li>
                    <li>Second significant achievement</li>
                    <li>Third major development</li>
                </ul>
                <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>";
    }
}