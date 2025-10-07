<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Activity;
use App\Models\User;
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
        return Inertia::render('Dashboard');
    }

    /**
     * Get dashboard statistics for the API.
     */
    public function stats(Request $request)
    {
        $since = $request->get('since');
        
        // Focus only on News statistics for now
        $stats = [
            'news' => [
                'total' => News::count(),
                'published' => News::where('status', 'published')->count(),
                'draft' => News::where('status', 'draft')->count(),
                'featured' => News::where('is_featured', true)->count(),
            ],
            'bids' => [
                'total' => 0,
                'active' => 0,
                'completed' => 0,
                'upcoming' => 0,
            ],
            'disclosure' => [
                'total' => 0,
                'published' => 0,
                'pending' => 0,
            ],
            'tourism' => [
                'total' => 0,
                'active' => 0,
                'featured' => 0,
                'upcoming' => 0,
            ],
            'awards' => [
                'total' => 0,
                'given' => 0,
                'pending' => 0,
                'categories' => 0,
            ],
            'sanggunian' => [
                'total' => 0,
                'active' => 0,
                'completed' => 0,
            ],
            'ordinance' => [
                'total' => 0,
                'passed' => 0,
                'pending' => 0,
            ],
            'users' => [
                'total' => User::count(),
                'active' => User::where('email_verified_at', '!=', null)->count(),
                'new' => User::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            ]
        ];

        // Recent News - only get new ones if since parameter provided
        $recentNewsQuery = News::with(['author' => function($query) {
            $query->select('id', 'name', 'email');
        }])->latest();
        
        if ($since) {
            $recentNewsQuery->where('created_at', '>', Carbon::parse($since));
        }
        
        $recentNews = $recentNewsQuery->limit(5)
            ->get(['id', 'title', 'category', 'status', 'is_featured', 'published_at', 'created_at', 'author_id']);

        // Recent Activity - only get new ones if since parameter provided
        $recentActivityQuery = Activity::with(['user' => function($query) {
            $query->select('id', 'name', 'email');
        }])->latest();
        
        if ($since) {
            $recentActivityQuery->where('created_at', '>', Carbon::parse($since));
        }
        
        $recentActivity = $recentActivityQuery->limit(10)->get();

        // Transform activities to match frontend expectations
        $transformedActivities = $recentActivity->map(function($activity) {
            return [
                'id' => $activity->id,
                'description' => $activity->description,
                'type' => $activity->type,
                'action' => $this->getActionFromDescription($activity->description),
                'user' => [
                    'id' => $activity->user->id ?? 1,
                    'name' => $activity->user->name ?? 'System',
                    'email' => $activity->user->email ?? 'system@example.com',
                    'avatar' => null, // Your User model doesn't have avatar field
                ],
                'created_at' => $activity->created_at->toISOString(),
            ];
        });

        // If no activities exist, create some sample ones based on news
        if ($transformedActivities->isEmpty() && !$recentNews->isEmpty()) {
            $transformedActivities = $recentNews->take(3)->map(function($news) {
                $authorName = $news->author->name ?? 'Unknown User';
                return [
                    'id' => $news->id + 1000, // Temporary ID
                    'description' => "Created news article: {$news->title}",
                    'type' => 'news',
                    'action' => 'created',
                    'user' => [
                        'id' => $news->author_id ?? 1,
                        'name' => $authorName,
                        'email' => $news->author->email ?? 'user@example.com',
                        'avatar' => null,
                    ],
                    'created_at' => $news->created_at->toISOString(),
                ];
            });
        }

        return response()->json([
            'stats' => $stats,
            'recentNews' => $recentNews,
            'recentBids' => [],
            'recentDisclosures' => [],
            'recentTourism' => [],
            'recentAwards' => [],
            'recentSanggunian' => [],
            'recentOrdinance' => [],
            'recentActivity' => $transformedActivities,
            'lastUpdated' => now()->toISOString(),
        ]);
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
        
        return 'updated'; // default
    }
}