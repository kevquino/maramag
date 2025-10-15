<?php
// app/Http/Controllers/BidsAwardsController.php

namespace App\Http\Controllers;

use App\Models\BidsAward;
use App\Models\News;
use App\Models\Activity;
use App\Models\User;
use App\Models\FullDisclosure;
use App\Models\TourismPackage;
use App\Models\AwardsRecognition;
use App\Models\SangguniangBayanMember;
use App\Models\OrdinanceResolution;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Inertia\Response;

class BidsAwardsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = auth()->user();
        
        // Check if user has bids_awards permission
        if (!$user->hasPermission('bids_awards') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to access bids & awards management.'
            ]);
        }

        $query = BidsAward::with('user')->latest();

        // Search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Bid type filter
        if ($request->has('bid_type') && $request->bid_type != '') {
            $query->where('bid_type', $request->bid_type);
        }

        // Get pagination parameter or default to 10
        $perPage = $request->get('per_page', 10);
        $bidsAwards = $query->paginate($perPage)
            ->withQueryString(); // This preserves the query parameters

        // Get badge counts for the sidebar/navigation
        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('BidsAwards/Index', [
            'bidsAwards' => $bidsAwards,
            'filters' => $request->only(['search', 'status', 'bid_type']),
            'statusOptions' => [
                'draft' => 'Draft',
                'published' => 'Published',
                'opened' => 'Opened',
                'evaluated' => 'Evaluated',
                'awarded' => 'Awarded',
                'cancelled' => 'Cancelled',
            ],
            'bidTypeOptions' => [
                'open_tender' => 'Open Tender',
                'closed_tender' => 'Closed Tender',
                'quotation' => 'Quotation',
                'rfp' => 'Request for Proposal',
            ],
            'badgeCounts' => $badgeCounts,
        ]);
    }

    public function create(): Response
    {
        $user = auth()->user();
        
        // Check if user has bids_awards permission
        if (!$user->hasPermission('bids_awards') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to create bids & awards.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('BidsAwards/Create', [
            'bidTypeOptions' => [
                'open_tender' => 'Open Tender',
                'closed_tender' => 'Closed Tender',
                'quotation' => 'Quotation',
                'rfp' => 'Request for Proposal',
            ],
            'statusOptions' => [
                'draft' => 'Draft',
                'published' => 'Published',
            ],
            'badgeCounts' => $badgeCounts,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Check if user has bids_awards permission
        if (!$user->hasPermission('bids_awards') && !$user->isAdmin()) {
            return redirect()->route('bids-awards.index')->with('error', 'You do not have permission to create bids & awards.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'reference_number' => 'required|string|unique:bids_awards,reference_number',
            'bid_type' => 'required|string',
            'estimated_budget' => 'nullable|numeric|min:0',
            'bid_opening_date' => 'nullable|date',
            'bid_closing_date' => 'required|date|after_or_equal:today',
            'status' => 'required|string',
            'is_featured' => 'boolean',
        ]);

        $validated['user_id'] = auth()->id();

        $bidsAward = BidsAward::create($validated);

        // Log activity
        Activity::create([
            'description' => "New bid/award created: {$bidsAward->title}",
            'type' => 'bids_awards',
            'user_id' => auth()->id(),
            'metadata' => [
                'bids_award_id' => $bidsAward->id,
                'title' => $bidsAward->title,
                'reference_number' => $bidsAward->reference_number,
                'action' => 'created'
            ]
        ]);

        return redirect()->route('bids-awards.index')
            ->with('success', 'Bid/Award created successfully.');
    }

    public function show(BidsAward $bidsAward): Response
    {
        $user = auth()->user();
        
        // Check if user has bids_awards permission
        if (!$user->hasPermission('bids_awards') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to view bids & awards.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('BidsAwards/Show', [
            'bidAward' => $bidsAward->load('user'),
            'badgeCounts' => $badgeCounts,
        ]);
    }

    public function edit(BidsAward $bidsAward): Response
    {
        $user = auth()->user();
        
        // Check if user has bids_awards permission
        if (!$user->hasPermission('bids_awards') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to edit bids & awards.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('BidsAwards/Edit', [
            'bidAward' => $bidsAward,
            'bidTypeOptions' => [
                'open_tender' => 'Open Tender',
                'closed_tender' => 'Closed Tender',
                'quotation' => 'Quotation',
                'rfp' => 'Request for Proposal',
            ],
            'statusOptions' => [
                'draft' => 'Draft',
                'published' => 'Published',
                'opened' => 'Opened',
                'evaluated' => 'Evaluated',
                'awarded' => 'Awarded',
                'cancelled' => 'Cancelled',
            ],
            'badgeCounts' => $badgeCounts,
        ]);
    }

    public function update(Request $request, BidsAward $bidsAward)
    {
        $user = auth()->user();
        
        // Check if user has bids_awards permission
        if (!$user->hasPermission('bids_awards') && !$user->isAdmin()) {
            return redirect()->route('bids-awards.index')->with('error', 'You do not have permission to update bids & awards.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'reference_number' => 'required|string|unique:bids_awards,reference_number,' . $bidsAward->id,
            'bid_type' => 'required|string',
            'estimated_budget' => 'nullable|numeric|min:0',
            'bid_opening_date' => 'nullable|date',
            'bid_closing_date' => 'required|date',
            'status' => 'required|string',
            'is_featured' => 'boolean',
            'awarded_to' => 'nullable|string|max:255',
            'awarded_amount' => 'nullable|numeric|min:0',
            'award_remarks' => 'nullable|string',
            'award_date' => 'nullable|date|required_if:status,awarded',
        ]);

        $bidsAward->update($validated);

        // Log activity
        Activity::create([
            'description' => "Bid/Award updated: {$bidsAward->title}",
            'type' => 'bids_awards',
            'user_id' => auth()->id(),
            'metadata' => [
                'bids_award_id' => $bidsAward->id,
                'title' => $bidsAward->title,
                'reference_number' => $bidsAward->reference_number,
                'action' => 'updated'
            ]
        ]);

        return redirect()->route('bids-awards.index')
            ->with('success', 'Bid/Award updated successfully.');
    }

    public function destroy(BidsAward $bidsAward)
    {
        $user = auth()->user();
        
        // Check if user has bids_awards permission
        if (!$user->hasPermission('bids_awards') && !$user->isAdmin()) {
            return redirect()->route('bids-awards.index')->with('error', 'You do not have permission to delete bids & awards.');
        }

        $bidsAwardTitle = $bidsAward->title;
        $referenceNumber = $bidsAward->reference_number;
        
        $bidsAward->delete();

        // Log activity
        Activity::create([
            'description' => "Bid/Award deleted: {$bidsAwardTitle}",
            'type' => 'bids_awards',
            'user_id' => auth()->id(),
            'metadata' => [
                'bids_award_id' => $bidsAward->id,
                'title' => $bidsAwardTitle,
                'reference_number' => $referenceNumber,
                'action' => 'deleted'
            ]
        ]);

        // For Inertia requests, return a redirect back to the index
        // with the current query parameters preserved
        return redirect()->route('bids-awards.index', request()->query())
            ->with('success', 'Bid/Award deleted successfully.');
    }

    public function toggleFeatured(BidsAward $bidsAward)
    {
        $user = auth()->user();
        
        // Check if user has bids_awards permission
        if (!$user->hasPermission('bids_awards') && !$user->isAdmin()) {
            return redirect()->route('bids-awards.index')->with('error', 'You do not have permission to toggle featured status.');
        }

        $bidsAward->update([
            'is_featured' => !$bidsAward->is_featured
        ]);

        $status = $bidsAward->is_featured ? 'featured' : 'unfeatured';

        // Log activity
        Activity::create([
            'description' => "Bid/Award {$status}: {$bidsAward->title}",
            'type' => 'bids_awards',
            'user_id' => auth()->id(),
            'metadata' => [
                'bids_award_id' => $bidsAward->id,
                'title' => $bidsAward->title,
                'reference_number' => $bidsAward->reference_number,
                'action' => 'featured_toggled',
                'is_featured' => $bidsAward->is_featured
            ]
        ]);

        // For Inertia, return a redirect back to preserve filters and pagination
        return redirect()->route('bids-awards.index', request()->query())
            ->with('success', 'Featured status updated successfully.');
    }

    /**
     * Get badge counts based on user permissions
     */
    private function getBadgeCounts(User $user): array
    {
        $badgeCounts = [];

        // Debug: Check user permissions
        \Log::debug('BidsAwardsController - User permissions check', [
            'user_id' => $user->id,
            'has_bids_awards_permission' => $user->hasPermission('bids_awards'),
            'is_admin' => $user->isAdmin(),
        ]);

        if ($user->hasPermission('news')) {
            $badgeCounts['news'] = News::where('status', 'published')->count();
            
            // Get trash count - News model uses SoftDeletes
            $trashCount = News::onlyTrashed()->count();
            $badgeCounts['trash'] = $trashCount;
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
            }
        }

        // Debug: Final badge counts
        \Log::debug('BidsAwardsController - Final badge counts', $badgeCounts);

        return $badgeCounts;
    }
}