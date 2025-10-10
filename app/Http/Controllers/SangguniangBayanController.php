<?php

namespace App\Http\Controllers;

use App\Models\SangguniangBayanMember;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class SangguniangBayanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SangguniangBayanMember::ordered();

        // Search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%")
                  ->orWhereJsonContains('committees', $search);
            });
        }

        // Position type filter
        if ($request->has('position_type') && $request->position_type != '') {
            $query->where('position_type', $request->position_type);
        }

        // Status filter
        if ($request->has('status') && $request->status != '') {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $members = $query->paginate(10)->withQueryString();

        return Inertia::render('SangguniangBayan/Index', [
            'members' => $members,
            'filters' => $request->only(['search', 'position_type', 'status']),
            'positionTypeOptions' => SangguniangBayanMember::getPositionTypes(),
            'statusOptions' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('SangguniangBayan/Create', [
            'positionTypeOptions' => SangguniangBayanMember::getPositionTypes(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'position_type' => 'required|string|in:regular,sk_president,liga_president,ip_representative',
            'bio' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048',
            'committees' => 'nullable|array',
            'committees.*' => 'string|max:255',
            'district' => 'nullable|string|max:255',
            'term_start' => 'nullable|date',
            'term_end' => 'nullable|date',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('sangguniang-bayan', 'public');
        }

        // Set default order (last position) - model will handle this automatically
        SangguniangBayanMember::create($validated);

        return redirect()->route('sangguniang-bayan.index')
            ->with('success', 'Sangguniang Bayan member created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SangguniangBayanMember $sangguniangBayan)
    {
        return Inertia::render('SangguniangBayan/Show', [
            'member' => $sangguniangBayan,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SangguniangBayanMember $sangguniangBayan)
    {
        return Inertia::render('SangguniangBayan/Edit', [
            'member' => $sangguniangBayan,
            'positionTypeOptions' => SangguniangBayanMember::getPositionTypes(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SangguniangBayanMember $sangguniangBayan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'position_type' => 'required|string|in:regular,sk_president,liga_president,ip_representative',
            'bio' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048',
            'committees' => 'nullable|array',
            'committees.*' => 'string|max:255',
            'district' => 'nullable|string|max:255',
            'term_start' => 'nullable|date',
            'term_end' => 'nullable|date',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($sangguniangBayan->photo) {
                Storage::disk('public')->delete($sangguniangBayan->photo);
            }
            $validated['photo'] = $request->file('photo')->store('sangguniang-bayan', 'public');
        }

        $sangguniangBayan->update($validated);

        return redirect()->route('sangguniang-bayan.index')
            ->with('success', 'Sangguniang Bayan member updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SangguniangBayanMember $sangguniangBayan)
    {
        // Delete photo if exists
        if ($sangguniangBayan->photo) {
            Storage::disk('public')->delete($sangguniangBayan->photo);
        }

        $sangguniangBayan->delete();

        return redirect()->route('sangguniang-bayan.index')
            ->with('success', 'Sangguniang Bayan member deleted successfully.');
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(SangguniangBayanMember $sangguniangBayan)
    {
        $sangguniangBayan->update([
            'is_featured' => !$sangguniangBayan->is_featured
        ]);

        $status = $sangguniangBayan->is_featured ? 'featured' : 'unfeatured';

        return back()->with('success', "Member {$status} successfully.");
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(SangguniangBayanMember $sangguniangBayan)
    {
        $sangguniangBayan->update([
            'is_active' => !$sangguniangBayan->is_active
        ]);

        $status = $sangguniangBayan->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Member {$status} successfully.");
    }

    /**
     * Update member order (Drag and Drop)
     */
    public function updateOrder(Request $request, $id)
    {
        try {
            $request->validate([
                'order' => 'required|integer|min:1'
            ]);

            $member = SangguniangBayanMember::findOrFail($id);
            $newOrder = $request->order;
            $oldOrder = $member->order;

            if ($newOrder === $oldOrder) {
                return back()->with('info', 'Order unchanged');
            }

            if ($newOrder < $oldOrder) {
                // Moving up - increment orders between new and current
                SangguniangBayanMember::whereBetween('order', [$newOrder, $oldOrder - 1])
                    ->increment('order');
            } else {
                // Moving down - decrement orders between current and new
                SangguniangBayanMember::whereBetween('order', [$oldOrder + 1, $newOrder])
                    ->decrement('order');
            }

            // Update the dragged member's order
            $member->order = $newOrder;
            $member->save();

            return back()->with('success', 'Member order updated successfully');

        } catch (\Exception $e) {
            \Log::error('Order update failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to update member order: ' . $e->getMessage());
        }
    }
}