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

class DashboardController extends Controller
{
    /**
     * Display the dashboard page.
     */
    public function index(): Response
    {
        $user = auth()->user();
        
        // REMOVED: Permission check for dashboard - now accessible to all authenticated users
        // All authenticated users can access the dashboard regardless of permissions

        $badgeCounts = [];
        
        // Calculate badge counts based on user permissions
        if (auth()->check()) {
            $badgeCounts = $this->getBadgeCounts($user);
        }

        return Inertia::render('Dashboard', [
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Get dashboard statistics for the API.
     */
    public function stats(Request $request)
    {
        $user = auth()->user();
        
        // REMOVED: Permission check for dashboard stats - now accessible to all authenticated users
        // All authenticated users can access dashboard statistics

        $since = $request->get('since');
        
        // Get statistics based on user permissions
        $stats = $this->getDashboardStats($user);
        
        // Get recent items based on user permissions
        $recentData = $this->getRecentData($user, $since);

        return response()->json([
            'stats' => $stats,
            'recentNews' => $recentData['recentNews'],
            'recentBids' => $recentData['recentBids'],
            'recentDisclosures' => $recentData['recentDisclosures'],
            'recentTourism' => $recentData['recentTourism'],
            'recentAwards' => $recentData['recentAwards'],
            'recentSanggunian' => $recentData['recentSanggunian'],
            'recentOrdinance' => $recentData['recentOrdinance'],
            'recentActivity' => $recentData['recentActivity'],
            'lastUpdated' => now()->toISOString(),
        ]);
    }

    /**
     * Get badge counts based on user permissions
     */
    private function getBadgeCounts(User $user): array
    {
        $badgeCounts = [];

        // Debug: Check user permissions
        \Log::debug('Dashboard - User permissions check', [
            'user_id' => $user->id,
            'has_news_permission' => $user->hasPermission('news'),
            'is_admin' => $user->isAdmin(),
        ]);

        if ($user->hasPermission('news')) {
            $badgeCounts['news'] = News::where('status', 'published')->count();
            
            // Get trash count - News model uses SoftDeletes
            $trashCount = News::onlyTrashed()->count();
            $badgeCounts['trash'] = $trashCount;
            
            // Debug: Log trash count
            \Log::debug('Dashboard - News trash count', [
                'trash_count' => $trashCount,
                'news_count' => $badgeCounts['news'],
            ]);
        }

        if ($user->hasPermission('bids_awards')) {
            $badgeCounts['bids_awards'] = BidsAward::count();
        }

        if ($user->hasPermission('full_disclosure')) {
            $badgeCounts['full_disclosure'] = FullDisclosure::count();
        }

        if ($user->hasPermission('tourism')) {
            $badgeCounts['tourism'] = TourismPackage::count();
        }

        if ($user->hasPermission('awards_recognition')) {
            $badgeCounts['awards_recognition'] = AwardsRecognition::count();
        }

        if ($user->hasPermission('sangguniang_bayan')) {
            $badgeCounts['sangguniang_bayan'] = SangguniangBayanMember::count();
        }

        if ($user->hasPermission('ordinance_resolutions')) {
            $badgeCounts['ordinance_resolutions'] = OrdinanceResolution::count();
        }

        if ($user->isAdmin()) {
            $badgeCounts['users'] = User::count();
            $badgeCounts['activity_logs'] = Activity::count();
            
            // Ensure admin also gets trash count even if they don't have explicit news permission
            if (!isset($badgeCounts['trash'])) {
                $trashCount = News::onlyTrashed()->count();
                $badgeCounts['trash'] = $trashCount;
                \Log::debug('Dashboard - Admin trash count', ['trash_count' => $trashCount]);
            }
        }

        // Debug: Final badge counts
        \Log::debug('Dashboard - Final badge counts', $badgeCounts);

        return $badgeCounts;
    }

    /**
     * Get dashboard statistics based on user permissions
     */
    private function getDashboardStats(User $user): array
    {
        $stats = [
            'news' => $user->hasPermission('news') ? [
                'total' => News::count(),
                'published' => News::where('status', 'published')->count(),
                'draft' => News::where('status', 'draft')->count(),
                'featured' => News::where('is_featured', true)->count(),
                'trash' => News::onlyTrashed()->count(),
            ] : null,

            'bids' => $user->hasPermission('bids_awards') ? [
                'total' => BidsAward::count(),
                'active' => BidsAward::where('status', 'active')->count(),
                'completed' => BidsAward::where('status', 'completed')->count(),
                'upcoming' => BidsAward::where('status', 'upcoming')->count(),
            ] : null,

            'disclosure' => $user->hasPermission('full_disclosure') ? [
                'total' => FullDisclosure::count(),
                'published' => FullDisclosure::where('status', 'published')->count(),
                'pending' => FullDisclosure::where('status', 'pending')->count(),
            ] : null,

            'tourism' => $user->hasPermission('tourism') ? [
                'total' => TourismPackage::count(),
                'active' => TourismPackage::where('status', 'active')->count(),
                'featured' => TourismPackage::where('is_featured', true)->count(),
                'upcoming' => TourismPackage::where('status', 'upcoming')->count(),
            ] : null,

            'awards' => $user->hasPermission('awards_recognition') ? [
                'total' => AwardsRecognition::count(),
                'given' => AwardsRecognition::where('status', 'given')->count(),
                'pending' => AwardsRecognition::where('status', 'pending')->count(),
                'categories' => AwardsRecognition::distinct('category')->count('category'),
            ] : null,

            'sanggunian' => $user->hasPermission('sangguniang_bayan') ? [
                'total' => SangguniangBayanMember::count(),
                'active' => SangguniangBayanMember::where('is_active', true)->count(),
                'inactive' => SangguniangBayanMember::where('is_active', false)->count(),
                'featured' => SangguniangBayanMember::where('is_featured', true)->count(),
            ] : null,

            'ordinance' => $user->hasPermission('ordinance_resolutions') ? [
                'total' => OrdinanceResolution::count(),
                'passed' => OrdinanceResolution::where('status', 'passed')->count(),
                'pending' => OrdinanceResolution::where('status', 'pending')->count(),
            ] : null,

            'users' => $user->isAdmin() ? [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'verified' => User::whereNotNull('email_verified_at')->count(),
                'new' => User::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            ] : null,
        ];

        // Remove null values for modules user doesn't have access to
        return array_filter($stats, function($value) {
            return $value !== null;
        });
    }

    /**
     * Get recent data based on user permissions
     */
    private function getRecentData(User $user, ?string $since = null): array
    {
        $recentData = [
            'recentNews' => [],
            'recentBids' => [],
            'recentDisclosures' => [],
            'recentTourism' => [],
            'recentAwards' => [],
            'recentSanggunian' => [],
            'recentOrdinance' => [],
            'recentActivity' => [],
        ];

        // Recent News
        if ($user->hasPermission('news')) {
            $recentNewsQuery = News::with(['author' => function($query) {
                $query->select('id', 'name', 'email');
            }])->latest();
            
            if ($since) {
                $recentNewsQuery->where('created_at', '>', Carbon::parse($since));
            }
            
            $recentData['recentNews'] = $recentNewsQuery->limit(5)
                ->get(['id', 'title', 'category', 'status', 'is_featured', 'published_at', 'created_at', 'author_id']);
        }

        // Recent Bids & Awards
        if ($user->hasPermission('bids_awards')) {
            $recentBidsQuery = BidsAward::latest();
            if ($since) {
                $recentBidsQuery->where('created_at', '>', Carbon::parse($since));
            }
            $recentData['recentBids'] = $recentBidsQuery->limit(5)->get();
        }

        // Recent Full Disclosures
        if ($user->hasPermission('full_disclosure')) {
            $recentDisclosuresQuery = FullDisclosure::latest();
            if ($since) {
                $recentDisclosuresQuery->where('created_at', '>', Carbon::parse($since));
            }
            $recentData['recentDisclosures'] = $recentDisclosuresQuery->limit(5)->get();
        }

        // Recent Tourism Packages
        if ($user->hasPermission('tourism')) {
            $recentTourismQuery = TourismPackage::latest();
            if ($since) {
                $recentTourismQuery->where('created_at', '>', Carbon::parse($since));
            }
            $recentData['recentTourism'] = $recentTourismQuery->limit(5)->get();
        }

        // Recent Awards & Recognition
        if ($user->hasPermission('awards_recognition')) {
            $recentAwardsQuery = AwardsRecognition::latest();
            if ($since) {
                $recentAwardsQuery->where('created_at', '>', Carbon::parse($since));
            }
            $recentData['recentAwards'] = $recentAwardsQuery->limit(5)->get();
        }

        // Recent Sangguniang Bayan Members
        if ($user->hasPermission('sangguniang_bayan')) {
            $recentSanggunianQuery = SangguniangBayanMember::latest();
            if ($since) {
                $recentSanggunianQuery->where('created_at', '>', Carbon::parse($since));
            }
            $recentData['recentSanggunian'] = $recentSanggunianQuery->limit(5)->get();
        }

        // Recent Ordinance & Resolutions
        if ($user->hasPermission('ordinance_resolutions')) {
            $recentOrdinanceQuery = OrdinanceResolution::latest();
            if ($since) {
                $recentOrdinanceQuery->where('created_at', '>', Carbon::parse($since));
            }
            $recentData['recentOrdinance'] = $recentOrdinanceQuery->limit(5)->get();
        }

        // Recent Activity
        if ($user->isAdmin() || $user->hasPermission('activity_logs')) {
            $recentActivityQuery = Activity::with(['user' => function($query) {
                $query->select('id', 'name', 'email');
            }])->latest();
            
            if ($since) {
                $recentActivityQuery->where('created_at', '>', Carbon::parse($since));
            }
            
            $recentActivity = $recentActivityQuery->limit(10)->get();

            $recentData['recentActivity'] = $recentActivity->map(function($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'type' => $activity->type,
                    'action' => $this->getActionFromDescription($activity->description),
                    'user' => [
                        'id' => $activity->user->id ?? 1,
                        'name' => $activity->user->name ?? 'System',
                        'email' => $activity->user->email ?? 'system@example.com',
                        'avatar' => null,
                    ],
                    'created_at' => $activity->created_at->toISOString(),
                ];
            });
        }

        return $recentData;
    }

    /**
     * Extract action from activity description
     */
    private function getActionFromDescription(string $description): string
    {
        $description = strtolower($description);
        
        if (str_contains($description, 'created') || str_contains($description, 'added')) {
            return 'created';
        }
        if (str_contains($description, 'updated') || str_contains($description, 'modified') || str_contains($description, 'edited')) {
            return 'updated';
        }
        if (str_contains($description, 'deleted') || str_contains($description, 'removed')) {
            return 'deleted';
        }
        if (str_contains($description, 'published')) {
            return 'published';
        }
        
        return 'updated';
    }
}