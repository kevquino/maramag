<?php

namespace App\Http\Controllers;

use App\Models\SangguniangBayanMember;
use App\Models\News;
use App\Models\Activity;
use App\Models\User;
use App\Models\BidsAward;
use App\Models\FullDisclosure;
use App\Models\TourismPackage;
use App\Models\AwardsRecognition;
use App\Models\OrdinanceResolution;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Inertia\Response;

class SangguniangBayanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        
        // Check if user has sangguniang_bayan permission
        if (!$user->hasPermission('sangguniang_bayan') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to access Sangguniang Bayan management.'
            ]);
        }

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

        // Get badge counts for the sidebar/navigation
        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('SangguniangBayan/Index', [
            'members' => $members,
            'filters' => $request->only(['search', 'position_type', 'status']),
            'positionTypeOptions' => SangguniangBayanMember::getPositionTypes(),
            'statusOptions' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $user = auth()->user();
        
        // Check if user has sangguniang_bayan permission
        if (!$user->hasPermission('sangguniang_bayan') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to create Sangguniang Bayan members.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('SangguniangBayan/Create', [
            'positionTypeOptions' => SangguniangBayanMember::getPositionTypes(),
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Check if user has sangguniang_bayan permission
        if (!$user->hasPermission('sangguniang_bayan') && !$user->isAdmin()) {
            return redirect()->route('sangguniang-bayan.index')->with('error', 'You do not have permission to create Sangguniang Bayan members.');
        }

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
        $member = SangguniangBayanMember::create($validated);

        // Log activity
        Activity::create([
            'description' => "Sangguniang Bayan member created: {$member->name}",
            'type' => 'sangguniang_bayan',
            'user_id' => auth()->id(),
            'metadata' => [
                'member_id' => $member->id,
                'name' => $member->name,
                'position' => $member->position,
                'position_type' => $member->position_type,
                'action' => 'created'
            ]
        ]);

        return redirect()->route('sangguniang-bayan.index')
            ->with('success', 'Sangguniang Bayan member created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SangguniangBayanMember $sangguniangBayan): Response
    {
        $user = auth()->user();
        
        // Check if user has sangguniang_bayan permission
        if (!$user->hasPermission('sangguniang_bayan') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to view Sangguniang Bayan members.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('SangguniangBayan/Show', [
            'member' => $sangguniangBayan,
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SangguniangBayanMember $sangguniangBayan): Response
    {
        $user = auth()->user();
        
        // Check if user has sangguniang_bayan permission
        if (!$user->hasPermission('sangguniang_bayan') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to edit Sangguniang Bayan members.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('SangguniangBayan/Edit', [
            'member' => $sangguniangBayan,
            'positionTypeOptions' => SangguniangBayanMember::getPositionTypes(),
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SangguniangBayanMember $sangguniangBayan)
    {
        $user = auth()->user();
        
        // Check if user has sangguniang_bayan permission
        if (!$user->hasPermission('sangguniang_bayan') && !$user->isAdmin()) {
            return redirect()->route('sangguniang-bayan.index')->with('error', 'You do not have permission to update Sangguniang Bayan members.');
        }

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

        // Log activity
        Activity::create([
            'description' => "Sangguniang Bayan member updated: {$sangguniangBayan->name}",
            'type' => 'sangguniang_bayan',
            'user_id' => auth()->id(),
            'metadata' => [
                'member_id' => $sangguniangBayan->id,
                'name' => $sangguniangBayan->name,
                'position' => $sangguniangBayan->position,
                'position_type' => $sangguniangBayan->position_type,
                'action' => 'updated'
            ]
        ]);

        return redirect()->route('sangguniang-bayan.index')
            ->with('success', 'Sangguniang Bayan member updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SangguniangBayanMember $sangguniangBayan)
    {
        $user = auth()->user();
        
        // Check if user has sangguniang_bayan permission
        if (!$user->hasPermission('sangguniang_bayan') && !$user->isAdmin()) {
            return redirect()->route('sangguniang-bayan.index')->with('error', 'You do not have permission to delete Sangguniang Bayan members.');
        }

        $memberName = $sangguniangBayan->name;
        $memberPosition = $sangguniangBayan->position;

        // Delete photo if exists
        if ($sangguniangBayan->photo) {
            Storage::disk('public')->delete($sangguniangBayan->photo);
        }

        $sangguniangBayan->delete();

        // Log activity
        Activity::create([
            'description' => "Sangguniang Bayan member deleted: {$memberName}",
            'type' => 'sangguniang_bayan',
            'user_id' => auth()->id(),
            'metadata' => [
                'member_id' => $sangguniangBayan->id,
                'name' => $memberName,
                'position' => $memberPosition,
                'action' => 'deleted'
            ]
        ]);

        return redirect()->route('sangguniang-bayan.index')
            ->with('success', 'Sangguniang Bayan member deleted successfully.');
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(SangguniangBayanMember $sangguniangBayan)
    {
        $user = auth()->user();
        
        // Check if user has sangguniang_bayan permission
        if (!$user->hasPermission('sangguniang_bayan') && !$user->isAdmin()) {
            return redirect()->route('sangguniang-bayan.index')->with('error', 'You do not have permission to toggle featured status.');
        }

        $sangguniangBayan->update([
            'is_featured' => !$sangguniangBayan->is_featured
        ]);

        $status = $sangguniangBayan->is_featured ? 'featured' : 'unfeatured';

        // Log activity
        Activity::create([
            'description' => "Sangguniang Bayan member {$status}: {$sangguniangBayan->name}",
            'type' => 'sangguniang_bayan',
            'user_id' => auth()->id(),
            'metadata' => [
                'member_id' => $sangguniangBayan->id,
                'name' => $sangguniangBayan->name,
                'action' => 'featured_toggled',
                'is_featured' => $sangguniangBayan->is_featured
            ]
        ]);

        return back()->with('success', "Member {$status} successfully.");
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(SangguniangBayanMember $sangguniangBayan)
    {
        $user = auth()->user();
        
        // Check if user has sangguniang_bayan permission
        if (!$user->hasPermission('sangguniang_bayan') && !$user->isAdmin()) {
            return redirect()->route('sangguniang-bayan.index')->with('error', 'You do not have permission to toggle status.');
        }

        $sangguniangBayan->update([
            'is_active' => !$sangguniangBayan->is_active
        ]);

        $status = $sangguniangBayan->is_active ? 'activated' : 'deactivated';

        // Log activity
        Activity::create([
            'description' => "Sangguniang Bayan member {$status}: {$sangguniangBayan->name}",
            'type' => 'sangguniang_bayan',
            'user_id' => auth()->id(),
            'metadata' => [
                'member_id' => $sangguniangBayan->id,
                'name' => $sangguniangBayan->name,
                'action' => 'status_toggled',
                'is_active' => $sangguniangBayan->is_active
            ]
        ]);

        return back()->with('success', "Member {$status} successfully.");
    }

    /**
     * Update member order (Drag and Drop)
     */
    public function updateOrder(Request $request, $id)
    {
        $user = auth()->user();
        
        // Check if user has sangguniang_bayan permission
        if (!$user->hasPermission('sangguniang_bayan') && !$user->isAdmin()) {
            return back()->with('error', 'You do not have permission to update member order.');
        }

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

            // Log activity
            Activity::create([
                'description' => "Sangguniang Bayan member order updated: {$member->name} (Position {$oldOrder} → {$newOrder})",
                'type' => 'sangguniang_bayan',
                'user_id' => auth()->id(),
                'metadata' => [
                    'member_id' => $member->id,
                    'name' => $member->name,
                    'old_order' => $oldOrder,
                    'new_order' => $newOrder,
                    'action' => 'order_updated'
                ]
            ]);

            return back()->with('success', 'Member order updated successfully');

        } catch (\Exception $e) {
            \Log::error('Order update failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to update member order: ' . $e->getMessage());
        }
    }

    /**
     * Get badge counts based on user permissions
     */
    private function getBadgeCounts(User $user): array
    {
        $badgeCounts = [];

        // Debug: Check user permissions
        \Log::debug('SangguniangBayanController - User permissions check', [
            'user_id' => $user->id,
            'has_sangguniang_bayan_permission' => $user->hasPermission('sangguniang_bayan'),
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
        \Log::debug('SangguniangBayanController - Final badge counts', $badgeCounts);

        return $badgeCounts;
    }
}