<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Activity;
use App\Models\User;
use App\Models\BidsAward;
use App\Models\FullDisclosure;
use App\Models\TourismPackage;
use App\Models\AwardsRecognition;
use App\Models\SangguniangBayanMember;
use App\Models\OrdinanceResolution;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Display the dashboard page.
     */
    public function index(): Response
    {
        $user = auth()->user();
        
        // Get badge counts for sidebar navigation
        $badgeCounts = [];
        
        if (auth()->check()) {
            $badgeCounts = Cache::remember('dashboard_badges_' . $user->id, 300, function () use ($user) {
                return $this->getBadgeCounts($user);
            });
        }

        return Inertia::render('Dashboard', [
            'badgeCounts' => $badgeCounts,
            'userRole' => $user->role,
        ]);
    }

    /**
     * Get dashboard statistics for the API.
     */
    public function stats(Request $request)
    {
        $user = auth()->user();
        
        $dashboardData = [
            'user' => [
                'name' => $user->name,
                'role' => $user->role,
                'office' => $user->office,
                'permissions' => $user->permissions,
            ],
            'quickActions' => $this->getQuickActions($user),
            'lastUpdated' => now()->toISOString(),
        ];

        // Add role-specific statistics
        if ($user->isSuperAdmin()) {
            $dashboardData['systemStats'] = Cache::remember('system_stats', 60, function () {
                return $this->getSystemStats();
            });
        }

        return response()->json($dashboardData);
    }

    /**
     * Get system-wide statistics for SuperAdmin
     */
    public function systemStats(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = Cache::remember('detailed_system_stats', 60, function () {
            return $this->getDetailedSystemStats();
        });

        return response()->json($stats);
    }

    /**
     * Get recent activities for dashboard
     */
    public function recentActivities(Request $request)
    {
        $user = auth()->user();
        $limit = $request->get('limit', 5);

        $activities = Cache::remember('recent_activities_' . $user->id, 30, function () use ($user, $limit) {
            return $this->getRecentActivities($user, $limit);
        });

        return response()->json($activities);
    }

    /**
     * Get badge counts based on user permissions
     */
    private function getBadgeCounts(User $user): array
    {
        $badgeCounts = [];

        // Debug: Check user permissions
        \Log::debug('DashboardController - User permissions check', [
            'user_id' => $user->id,
            'is_superadmin' => $user->isSuperAdmin(),
            'is_admin' => $user->isAdmin(),
            'permissions' => $user->permissions,
        ]);

        // Superadmin and users with specific permissions get badge counts
        if ($user->isSuperAdmin() || $user->hasPermission('news')) {
            $badgeCounts['news'] = News::where('status', 'published')->count();
            
            // Get trash count - News model uses SoftDeletes
            $trashCount = News::onlyTrashed()->count();
            $badgeCounts['trash'] = $trashCount;
        }

        if ($user->isSuperAdmin() || $user->hasPermission('bids_awards')) {
            $badgeCounts['bids_awards'] = BidsAward::count();
        }

        if ($user->isSuperAdmin() || $user->hasPermission('full_disclosure')) {
            $badgeCounts['full_disclosure'] = FullDisclosure::count();
        }

        if ($user->isSuperAdmin() || $user->hasPermission('tourism')) {
            $badgeCounts['tourism'] = TourismPackage::count();
        }

        if ($user->isSuperAdmin() || $user->hasPermission('awards_recognition')) {
            $badgeCounts['awards_recognition'] = AwardsRecognition::count();
        }

        if ($user->isSuperAdmin() || $user->hasPermission('sangguniang_bayan')) {
            $badgeCounts['sangguniang_bayan'] = SangguniangBayanMember::count();
        }

        if ($user->isSuperAdmin() || $user->hasPermission('ordinance_resolutions')) {
            $badgeCounts['ordinance_resolutions'] = OrdinanceResolution::count();
        }

        if ($user->isSuperAdmin() || $user->hasPermission('user_management')) {
            $badgeCounts['users'] = User::count();
        }

        if ($user->isSuperAdmin() || $user->hasPermission('activity_logs')) {
            $badgeCounts['activity_logs'] = Activity::count();
        }

        // Ensure superadmin gets all badge counts even if they don't have explicit permissions
        if ($user->isSuperAdmin()) {
            // Make sure all badge counts are set
            $allBadges = [
                'news' => News::where('status', 'published')->count(),
                'bids_awards' => BidsAward::count(),
                'full_disclosure' => FullDisclosure::count(),
                'tourism' => TourismPackage::count(),
                'awards_recognition' => AwardsRecognition::count(),
                'sangguniang_bayan' => SangguniangBayanMember::count(),
                'ordinance_resolutions' => OrdinanceResolution::count(),
                'users' => User::count(),
                'activity_logs' => Activity::count(),
                'trash' => News::onlyTrashed()->count(),
            ];
            
            $badgeCounts = array_merge($allBadges, $badgeCounts);
        }

        // Debug: Final badge counts
        \Log::debug('DashboardController - Final badge counts', $badgeCounts);

        return $badgeCounts;
    }

    /**
     * Get quick actions based on user permissions
     */
    private function getQuickActions(User $user): array
    {
        $actions = [];

        if ($user->hasPermission('news')) {
            $actions[] = [
                'title' => 'Create News',
                'description' => 'Publish new articles and updates',
                'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                'route' => '/news/create',
                'color' => 'text-blue-600',
                'bgColor' => 'bg-blue-100 dark:bg-blue-900'
            ];
        }

        if ($user->hasPermission('bids_awards')) {
            $actions[] = [
                'title' => 'Manage Bids',
                'description' => 'Oversee bids and awards process',
                'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                'route' => '/bids-awards',
                'color' => 'text-green-600',
                'bgColor' => 'bg-green-100 dark:bg-green-900'
            ];
        }

        if ($user->hasPermission('tourism')) {
            $actions[] = [
                'title' => 'Tourism Packages',
                'description' => 'Manage tourism offerings',
                'icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'route' => '/tourism',
                'color' => 'text-orange-600',
                'bgColor' => 'bg-orange-100 dark:bg-orange-900'
            ];
        }

        if ($user->hasPermission('business_permit')) {
            $actions[] = [
                'title' => 'Business Permits',
                'description' => 'Process permit applications',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'route' => '/business-permit',
                'color' => 'text-purple-600',
                'bgColor' => 'bg-purple-100 dark:bg-purple-900'
            ];
        }

        if ($user->hasPermission('full_disclosure')) {
            $actions[] = [
                'title' => 'Full Disclosure',
                'description' => 'Manage public documents',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'route' => '/full-disclosure',
                'color' => 'text-indigo-600',
                'bgColor' => 'bg-indigo-100 dark:bg-indigo-900'
            ];
        }

        if ($user->hasPermission('awards_recognition')) {
            $actions[] = [
                'title' => 'Awards & Recognition',
                'description' => 'Manage awards program',
                'icon' => 'M11.048 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
                'route' => '/awards-recognition',
                'color' => 'text-red-600',
                'bgColor' => 'bg-red-100 dark:bg-red-900'
            ];
        }

        if ($user->hasPermission('sangguniang_bayan')) {
            $actions[] = [
                'title' => 'Sangguniang Bayan',
                'description' => 'Manage legislative members',
                'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                'route' => '/sangguniang-bayan',
                'color' => 'text-cyan-600',
                'bgColor' => 'bg-cyan-100 dark:bg-cyan-900'
            ];
        }

        if ($user->hasPermission('ordinance_resolutions')) {
            $actions[] = [
                'title' => 'Ordinance & Resolutions',
                'description' => 'Manage legislative documents',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'route' => '/ordinance-resolutions',
                'color' => 'text-emerald-600',
                'bgColor' => 'bg-emerald-100 dark:bg-emerald-900'
            ];
        }

        if ($user->hasPermission('user_management') || $user->isSuperAdmin()) {
            $actions[] = [
                'title' => 'User Management',
                'description' => 'Manage system users',
                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z',
                'route' => '/user-management',
                'color' => 'text-pink-600',
                'bgColor' => 'bg-pink-100 dark:bg-pink-900'
            ];
        }

        return $actions;
    }

    /**
     * Get system-wide statistics
     */
    private function getSystemStats(): array
    {
        // Check column existence to avoid errors
        $tourismHasStatus = Schema::hasColumn('tourism_packages', 'status');
        $tourismHasIsActive = Schema::hasColumn('tourism_packages', 'is_active');
        $awardsHasStatus = Schema::hasColumn('awards_recognitions', 'status');
        $awardsHasIsActive = Schema::hasColumn('awards_recognitions', 'is_active');
        $sbHasStatus = Schema::hasColumn('sangguniang_bayan_members', 'status');
        $sbHasIsActive = Schema::hasColumn('sangguniang_bayan_members', 'is_active');

        return [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(), // Use is_active instead of status
            'totalNews' => News::where('status', 'published')->count(),
            'totalBids' => BidsAward::count(),
            'totalTourism' => TourismPackage::count(),
            'activeTourism' => $tourismHasStatus ? 
                TourismPackage::where('status', 'active')->count() : 
                ($tourismHasIsActive ? 
                    TourismPackage::where('is_active', true)->count() : 
                    TourismPackage::count()),
            'systemHealth' => 'optimal',
            'lastBackup' => now()->subDays(1)->format('Y-m-d H:i:s'),
            'serverUptime' => '99.9%',
        ];
    }

    /**
     * Get detailed system statistics for SuperAdmin
     */
    private function getDetailedSystemStats(): array
    {
        // Check column existence to avoid errors
        $tourismHasStatus = Schema::hasColumn('tourism_packages', 'status');
        $tourismHasIsActive = Schema::hasColumn('tourism_packages', 'is_active');
        $awardsHasStatus = Schema::hasColumn('awards_recognitions', 'status');
        $awardsHasIsActive = Schema::hasColumn('awards_recognitions', 'is_active');
        $sbHasStatus = Schema::hasColumn('sangguniang_bayan_members', 'status');
        $sbHasIsActive = Schema::hasColumn('sangguniang_bayan_members', 'is_active');

        return [
            'users' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(), // Use is_active
                'pending' => 0, // No pending status
                'inactive' => User::where('is_active', false)->count(), // Use is_active
                'superadmins' => User::where('role', 'superadmin')->count(),
                'admins' => User::where('role', 'admin')->count(),
                'staff' => User::where('role', 'staff')->count(),
                'users' => User::where('role', 'user')->count(),
            ],
            'content' => [
                'news' => News::count(),
                'published_news' => News::where('status', 'published')->count(),
                'bids_awards' => BidsAward::count(),
                'tourism_packages' => TourismPackage::count(),
                'active_tourism' => $tourismHasStatus ? 
                    TourismPackage::where('status', 'active')->count() : 
                    ($tourismHasIsActive ? 
                        TourismPackage::where('is_active', true)->count() : 
                        TourismPackage::count()),
                'awards' => AwardsRecognition::count(),
                'active_awards' => $awardsHasStatus ? 
                    AwardsRecognition::where('status', 'active')->count() : 
                    ($awardsHasIsActive ? 
                        AwardsRecognition::where('is_active', true)->count() : 
                        AwardsRecognition::count()),
                'sb_members' => SangguniangBayanMember::count(),
                'active_sb_members' => $sbHasStatus ? 
                    SangguniangBayanMember::where('status', 'active')->count() : 
                    ($sbHasIsActive ? 
                        SangguniangBayanMember::where('is_active', true)->count() : 
                        SangguniangBayanMember::count()),
                'ordinances' => OrdinanceResolution::where('type', 'ordinance')->count(),
                'resolutions' => OrdinanceResolution::where('type', 'resolution')->count(),
                'disclosures' => FullDisclosure::count(),
            ],
            'system' => [
                'activities' => Activity::count(),
                'storage_used' => '2.3GB',
                'database_size' => '1.2GB',
                'backup_status' => 'success',
                'last_maintenance' => now()->subDays(2)->format('Y-m-d H:i:s'),
            ]
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities(User $user, int $limit = 5): array
    {
        $query = Activity::with('user')->latest();
        
        // For non-superadmins, only show their activities
        if (!$user->isSuperAdmin()) {
            $query->where('user_id', $user->id);
        }
        
        return $query->limit($limit)->get()->map(function ($activity) {
            return [
                'id' => $activity->id,
                'action' => $activity->action,
                'description' => $activity->description,
                'time' => $activity->created_at->diffForHumans(),
                'user' => $activity->user->name,
                'icon' => $this->getActivityIcon($activity->action),
                'color' => $this->getActivityColor($activity->action),
            ];
        })->toArray();
    }

    /**
     * Get icon for activity type
     */
    private function getActivityIcon(string $action): string
    {
        return match (true) {
            str_contains($action, 'login') => '🔐',
            str_contains($action, 'create') => '📝',
            str_contains($action, 'update') => '✏️',
            str_contains($action, 'delete') => '🗑️',
            default => '📋'
        };
    }

    /**
     * Get color for activity type
     */
    private function getActivityColor(string $action): string
    {
        return match (true) {
            str_contains($action, 'login') => 'text-green-600',
            str_contains($action, 'create') => 'text-blue-600',
            str_contains($action, 'update') => 'text-yellow-600',
            str_contains($action, 'delete') => 'text-red-600',
            default => 'text-gray-600'
        };
    }
}