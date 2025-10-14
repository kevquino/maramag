<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Inertia\Inertia;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Share common data with all Inertia responses
     */
    public function __construct()
    {
        $this->shareBadgeCounts();
    }

    /**
     * Share badge counts with frontend
     */
    protected function shareBadgeCounts()
    {
        Inertia::share('badgeCounts', function () {
            // Only calculate if user is authenticated
            if (!Auth::check()) {
                return [];
            }

            try {
                return [
                    'news' => News::where('status', 'published')->count(),
                    // 'bids' => 0, // Commented out - no model yet
                    // 'new_applications' => 0, // Commented out - no model yet
                    // 'renewal_applications' => 0, // Commented out - no model yet
                    // 'ordinances' => 0, // Commented out - no model yet
                    // 'activity_logs' => 0, // Commented out - no model yet
                    'trash' => News::onlyTrashed()->count(), // Only news trash for now
                ];
            } catch (\Exception $e) {
                // Return empty counts if there's any database issue
                return [
                    'news' => 0,
                    // 'bids' => 0,
                    // 'new_applications' => 0,
                    // 'renewal_applications' => 0,
                    // 'ordinances' => 0,
                    // 'activity_logs' => 0,
                    'trash' => 0,
                ];
            }
        });
    }
}