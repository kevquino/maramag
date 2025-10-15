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
        // Get users from the database - with fallbacks
        $admin = User::where('email', 'admin@municipality.gov')->first();
        $pioOfficer = User::where('email', 'pio.officer@municipality.gov')->first();
        $pioStaff = User::where('email', 'pio.staff@municipality.gov')->first();
        $mayorAdmin = User::where('email', 'mayor.admin@municipality.gov')->first();
        
        // Fallback: if specific users aren't found, use admin as author
        $tourismAdmin = User::where('email', 'tourism.admin@municipality.gov')->first() ?? $admin;
        $healthAdmin = User::where('email', 'health.admin@municipality.gov')->first() ?? $admin;

        // If no users exist at all, create a fallback admin user
        if (!$admin) {
            $admin = User::create([
                'name' => 'System Administrator',
                'email' => 'admin@municipality.gov',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'office' => 'Municipal Mayor\'s Office',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        $news = [
            [
                'title' => 'Municipality Launches New Infrastructure Projects',
                'slug' => 'municipality-launches-new-infrastructure-projects',
                'excerpt' => 'The local government announces the start of three major infrastructure projects aimed at improving public facilities and road networks.',
                'content' => $this->generateContent('Municipal Infrastructure Projects'),
                'published_at' => Carbon::parse('2024-01-15'),
                'author_id' => $mayorAdmin ? $mayorAdmin->id : $admin->id,
                'category' => 'Infrastructure', // 12 chars
                'status' => 'published',
                'is_featured' => true,
                'image_path' => null,
            ],
            [
                'title' => 'Annual Town Fiesta Schedule Released',
                'slug' => 'annual-town-fiesta-schedule-released',
                'excerpt' => 'The Municipal Tourism Office has released the official schedule for the upcoming town fiesta celebration.',
                'content' => $this->generateContent('Town Fiesta Celebration'),
                'published_at' => Carbon::parse('2024-01-10'),
                'author_id' => $tourismAdmin->id,
                'category' => 'Tourism', // 7 chars
                'status' => 'published',
                'is_featured' => true,
                'image_path' => null,
            ],
            [
                'title' => 'Free Medical Mission for Senior Citizens',
                'slug' => 'free-medical-mission-for-senior-citizens',
                'excerpt' => 'The Municipal Health Office will conduct a free medical mission for senior citizens next week.',
                'content' => $this->generateContent('Medical Mission for Seniors'),
                'published_at' => Carbon::parse('2024-01-08'),
                'author_id' => $healthAdmin->id,
                'category' => 'Health', // 6 chars
                'status' => 'published',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'Public Consultation on New Municipal Ordinance',
                'slug' => 'public-consultation-on-new-municipal-ordinance',
                'excerpt' => 'The Sangguniang Bayan invites the public to a consultation meeting regarding the proposed environmental protection ordinance.',
                'content' => $this->generateContent('Public Consultation on Ordinance'),
                'published_at' => Carbon::parse('2024-01-05'),
                'author_id' => $pioOfficer ? $pioOfficer->id : $admin->id,
                'category' => 'Government', // 10 chars
                'status' => 'published',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'Municipality Receives Good Governance Award',
                'slug' => 'municipality-receives-good-governance-award',
                'excerpt' => 'The local government unit has been recognized for excellence in public service and transparency.',
                'content' => $this->generateContent('Good Governance Award'),
                'published_at' => Carbon::parse('2024-01-03'),
                'author_id' => $admin->id,
                'category' => 'Awards', // 6 chars
                'status' => 'published',
                'is_featured' => true,
                'image_path' => null,
            ],
            [
                'title' => 'Scholarship Program Applications Now Open',
                'slug' => 'scholarship-program-applications-now-open',
                'excerpt' => 'The Municipal Social Welfare Office is now accepting applications for the educational scholarship program.',
                'content' => $this->generateContent('Scholarship Program'),
                'published_at' => Carbon::parse('2024-01-01'),
                'author_id' => $pioStaff ? $pioStaff->id : $admin->id,
                'category' => 'Education', // 9 chars
                'status' => 'published',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'Road Rehabilitation Project Update',
                'slug' => 'road-rehabilitation-project-update',
                'excerpt' => 'Updates on the ongoing road rehabilitation project in the central business district.',
                'content' => $this->generateContent('Road Rehabilitation Project'),
                'published_at' => Carbon::parse('2023-12-28'),
                'author_id' => $pioOfficer ? $pioOfficer->id : $admin->id,
                'category' => 'Infrastructure',
                'status' => 'published',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'New Public Market Construction Begins',
                'slug' => 'new-public-market-construction-begins',
                'excerpt' => 'Construction of the modern public market facility has officially started.',
                'content' => $this->generateContent('Public Market Construction'),
                'published_at' => Carbon::parse('2023-12-25'),
                'author_id' => $mayorAdmin ? $mayorAdmin->id : $admin->id,
                'category' => 'Infrastructure',
                'status' => 'published',
                'is_featured' => true,
                'image_path' => null,
            ],
            [
                'title' => 'Christmas Celebration Events Schedule',
                'slug' => 'christmas-celebration-events-schedule',
                'excerpt' => 'Complete schedule of Christmas events and activities organized by the municipality.',
                'content' => $this->generateContent('Christmas Events Schedule'),
                'published_at' => Carbon::parse('2023-12-20'),
                'author_id' => $tourismAdmin->id,
                'category' => 'Events', // 6 chars
                'status' => 'archived',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'Water System Improvement Project',
                'slug' => 'water-system-improvement-project',
                'excerpt' => 'The municipality launches a major water system improvement project to ensure clean water supply.',
                'content' => $this->generateContent('Water System Improvement'),
                'published_at' => Carbon::parse('2023-12-15'),
                'author_id' => $pioOfficer ? $pioOfficer->id : $admin->id,
                'category' => 'Infrastructure',
                'status' => 'published',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'Job Fair 2024 Announcement',
                'slug' => 'job-fair-2024-announcement',
                'excerpt' => 'PESO announces the annual job fair with participation from major employers.',
                'content' => $this->generateContent('Job Fair 2024'),
                'published_at' => Carbon::parse('2023-12-10'),
                'author_id' => $pioStaff ? $pioStaff->id : $admin->id,
                'category' => 'Employment', // 9 chars
                'status' => 'published',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'Environmental Clean-up Drive Results',
                'slug' => 'environmental-clean-up-drive-results',
                'excerpt' => 'Successful community clean-up drive collects over 500 kilos of waste.',
                'content' => $this->generateContent('Clean-up Drive Results'),
                'published_at' => Carbon::parse('2023-12-05'),
                'author_id' => $healthAdmin->id,
                'category' => 'Environment', // 10 chars
                'status' => 'published',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'Tax Amnesty Program Extended',
                'slug' => 'tax-amnesty-program-extended',
                'excerpt' => 'The Municipal Treasurer announces extension of the tax amnesty program until end of month.',
                'content' => $this->generateContent('Tax Amnesty Extension'),
                'published_at' => Carbon::parse('2023-12-01'),
                'author_id' => $pioOfficer ? $pioOfficer->id : $admin->id,
                'category' => 'Finance', // 7 chars
                'status' => 'published',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'New Public Library Opening Soon',
                'slug' => 'new-public-library-opening-soon',
                'excerpt' => 'The modern public library facility is set to open next month with enhanced services.',
                'content' => $this->generateContent('Public Library Opening'),
                'published_at' => Carbon::parse('2023-11-28'),
                'author_id' => $admin->id,
                'category' => 'Education',
                'status' => 'draft',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'Disaster Preparedness Training Schedule',
                'slug' => 'disaster-preparedness-training-schedule',
                'excerpt' => 'The Municipal DRRMO releases schedule for community disaster preparedness training.',
                'content' => $this->generateContent('Disaster Preparedness Training'),
                'published_at' => Carbon::parse('2023-11-25'),
                'author_id' => $pioStaff ? $pioStaff->id : $admin->id,
                'category' => 'Safety', // 6 chars
                'status' => 'published',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'Agricultural Support Program Launch',
                'slug' => 'agricultural-support-program-launch',
                'excerpt' => 'New support program for local farmers includes training and equipment assistance.',
                'content' => $this->generateContent('Agricultural Support Program'),
                'published_at' => Carbon::parse('2023-11-20'),
                'author_id' => $pioOfficer ? $pioOfficer->id : $admin->id,
                'category' => 'Agriculture', // 10 chars
                'status' => 'published',
                'is_featured' => true,
                'image_path' => null,
            ],
            [
                'title' => 'Traffic Management System Upgrade',
                'slug' => 'traffic-management-system-upgrade',
                'excerpt' => 'Implementation of new traffic management system to improve flow in urban areas.',
                'content' => $this->generateContent('Traffic System Upgrade'),
                'published_at' => Carbon::parse('2023-11-15'),
                'author_id' => $mayorAdmin ? $mayorAdmin->id : $admin->id,
                'category' => 'Transport', // 8 chars
                'status' => 'published',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'Cultural Heritage Month Celebration',
                'slug' => 'cultural-heritage-month-celebration',
                'excerpt' => 'Month-long celebration of local culture and heritage features various activities.',
                'content' => $this->generateContent('Cultural Heritage Celebration'),
                'published_at' => Carbon::parse('2023-11-10'),
                'author_id' => $tourismAdmin->id,
                'category' => 'Culture', // 7 chars
                'status' => 'published',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'Business Permit Renewal Reminder',
                'slug' => 'business-permit-renewal-reminder',
                'excerpt' => 'Reminder to all business owners about the upcoming permit renewal deadline.',
                'content' => $this->generateContent('Business Permit Renewal'),
                'published_at' => Carbon::parse('2023-11-05'),
                'author_id' => $pioOfficer ? $pioOfficer->id : $admin->id,
                'category' => 'Business', // 8 chars
                'status' => 'published',
                'is_featured' => false,
                'image_path' => null,
            ],
            [
                'title' => 'Sports Complex Renovation Complete',
                'slug' => 'sports-complex-renovation-complete',
                'excerpt' => 'The newly renovated municipal sports complex is now open to the public.',
                'content' => $this->generateContent('Sports Complex Renovation'),
                'published_at' => Carbon::parse('2023-11-01'),
                'author_id' => $admin->id,
                'category' => 'Sports', // 6 chars
                'status' => 'published',
                'is_featured' => true,
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
                <p>This is an official announcement from the Municipal Government regarding {$title}. The local government unit is committed to providing timely and accurate information to all constituents.</p>
                
                <h2>Project Details</h2>
                <p>The initiative focuses on improving public services and enhancing the quality of life for all residents. Through collaborative efforts between different municipal offices, we aim to achieve significant progress in community development.</p>
                
                <h2>Key Objectives</h2>
                <ul>
                    <li>Enhance public service delivery</li>
                    <li>Promote transparency and good governance</li>
                    <li>Encourage community participation</li>
                    <li>Ensure sustainable development</li>
                </ul>
                
                <h2>Implementation Timeline</h2>
                <p>The project will be implemented in phases, with regular updates provided to the public. Monitoring and evaluation mechanisms are in place to ensure project success.</p>
                
                <h2>Community Benefits</h2>
                <p>Residents can expect improved access to essential services, better infrastructure, and enhanced opportunities for growth and development. The municipal government remains dedicated to serving the best interests of the community.</p>
                
                <h2>Contact Information</h2>
                <p>For more information, please contact the Public Information Office or visit the Municipal Hall during office hours. Follow our official social media pages for regular updates.</p>
                
                <p><strong>Together, let's build a progressive and resilient community!</strong></p>";
    }
}