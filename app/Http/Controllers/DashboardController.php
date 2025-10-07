<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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
    public function stats()
    {
        $stats = [
            'news' => [
                'total' => News::count(),
                'published' => News::where('status', 'published')->count(),
                'draft' => News::where('status', 'draft')->count(),
                'featured' => News::where('is_featured', true)->count(),
            ],
            'bids' => [
                'total' => 0, // Replace with your bid model
                'active' => 0,
                'completed' => 0,
                'upcoming' => 0,
            ],
            'disclosure' => [
                'total' => 0, // Replace with your disclosure model
                'published' => 0,
                'pending' => 0,
            ],
            'tourism' => [
                'total' => 0, // Replace with your tourism model
                'active' => 0,
                'featured' => 0,
                'upcoming' => 0,
            ]
        ];

        $recentNews = News::with('author')
            ->latest()
            ->limit(5)
            ->get(['id', 'title', 'category', 'status', 'is_featured', 'published_at', 'created_at']);

        // Sample data - replace with your actual models
        $recentBids = []; // Bid::latest()->limit(3)->get();
        $recentDisclosures = []; // Disclosure::latest()->limit(3)->get();
        $recentTourism = []; // Tourism::latest()->limit(3)->get();
        $recentActivity = []; // Activity::latest()->limit(10)->get();

        return response()->json([
            'stats' => $stats,
            'recentNews' => $recentNews,
            'recentBids' => $recentBids,
            'recentDisclosures' => $recentDisclosures,
            'recentTourism' => $recentTourism,
            'recentActivity' => $recentActivity,
        ]);
    }
}