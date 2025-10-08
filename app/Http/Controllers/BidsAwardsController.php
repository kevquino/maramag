<?php
// app/Http/Controllers/BidsAwardsController.php

namespace App\Http\Controllers;

use App\Models\BidsAward;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class BidsAwardsController extends Controller
{
    public function index(Request $request)
    {
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
        ]);
    }

    public function create()
    {
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
        ]);
    }

    public function store(Request $request)
    {
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

        BidsAward::create($validated);

        return redirect()->route('bids-awards.index')
            ->with('success', 'Bid/Award created successfully.');
    }

    public function show(BidsAward $bidsAward)
    {
        return Inertia::render('BidsAwards/Show', [
            'bidAward' => $bidsAward->load('user'),
        ]);
    }

    public function edit(BidsAward $bidsAward)
    {
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
        ]);
    }

    public function update(Request $request, BidsAward $bidsAward)
    {
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

        return redirect()->route('bids-awards.index')
            ->with('success', 'Bid/Award updated successfully.');
    }

    public function destroy(BidsAward $bidsAward)
    {
        $bidsAward->delete();

        // For Inertia requests, return a redirect back to the index
        // with the current query parameters preserved
        return redirect()->route('bids-awards.index', request()->query())
            ->with('success', 'Bid/Award deleted successfully.');
    }

    public function toggleFeatured(BidsAward $bidsAward)
    {
        $bidsAward->update([
            'is_featured' => !$bidsAward->is_featured
        ]);

        // For Inertia, return a redirect back to preserve filters and pagination
        return redirect()->route('bids-awards.index', request()->query())
            ->with('success', 'Featured status updated successfully.');
    }
}