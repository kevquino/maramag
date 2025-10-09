<?php
// app/Http/Controllers/SangguniangBayanController.php

namespace App\Http\Controllers;

use App\Models\SangguniangBayanMember;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SangguniangBayanController extends Controller
{
    public function index(Request $request)
    {
        $query = SangguniangBayanMember::query();

        // Search filter
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('position', 'like', '%' . $request->search . '%')
                  ->orWhere('position_type', 'like', '%' . $request->search . '%');
        }

        // Position type filter
        if ($request->has('position_type') && $request->position_type) {
            $query->where('position_type', $request->position_type);
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('is_active', $request->status === 'active');
        }

        $members = $query->orderBy('order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

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

    public function create()
    {
        return Inertia::render('SangguniangBayan/Create', [
            'positionTypeOptions' => SangguniangBayanMember::getPositionTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'position_type' => 'required|in:' . implode(',', array_keys(SangguniangBayanMember::getPositionTypes())),
            'bio' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'order' => 'nullable|integer',
            'committees' => 'nullable|array',
            'committees.*' => 'string|max:255',
            'district' => 'nullable|string|max:255',
            'term_start' => 'nullable|string|max:255',
            'term_end' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('sangguniang-bayan', 'public');
        }

        // Set default order if not provided
        if (!isset($data['order'])) {
            $maxOrder = SangguniangBayanMember::max('order');
            $data['order'] = $maxOrder ? $maxOrder + 1 : 1;
        }

        SangguniangBayanMember::create($data);

        return redirect()->route('sangguniang-bayan.index')
            ->with('success', 'Sangguniang Bayan member created successfully.');
    }

    public function show(SangguniangBayanMember $sangguniangBayan)
    {
        return Inertia::render('SangguniangBayan/Show', [
            'member' => $sangguniangBayan,
        ]);
    }

    public function edit(SangguniangBayanMember $sangguniangBayan)
    {
        return Inertia::render('SangguniangBayan/Edit', [
            'member' => $sangguniangBayan,
            'positionTypeOptions' => SangguniangBayanMember::getPositionTypes(),
        ]);
    }

    public function update(Request $request, SangguniangBayanMember $sangguniangBayan)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'position_type' => 'required|in:' . implode(',', array_keys(SangguniangBayanMember::getPositionTypes())),
            'bio' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'order' => 'nullable|integer',
            'committees' => 'nullable|array',
            'committees.*' => 'string|max:255',
            'district' => 'nullable|string|max:255',
            'term_start' => 'nullable|string|max:255',
            'term_end' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($sangguniangBayan->photo) {
                Storage::disk('public')->delete($sangguniangBayan->photo);
            }
            $data['photo'] = $request->file('photo')->store('sangguniang-bayan', 'public');
        }

        $sangguniangBayan->update($data);

        return redirect()->route('sangguniang-bayan.index')
            ->with('success', 'Sangguniang Bayan member updated successfully.');
    }

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

    public function toggleFeatured(SangguniangBayanMember $sangguniangBayan)
    {
        $sangguniangBayan->update([
            'is_featured' => !$sangguniangBayan->is_featured
        ]);

        $action = $sangguniangBayan->is_featured ? 'featured' : 'unfeatured';

        return redirect()->back()
            ->with('success', "Member {$action} successfully.");
    }

    public function toggleStatus(SangguniangBayanMember $sangguniangBayan)
    {
        $sangguniangBayan->update([
            'is_active' => !$sangguniangBayan->is_active
        ]);

        $action = $sangguniangBayan->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Member {$action} successfully.");
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:sangguniang_bayan_members,id',
            'order.*.order' => 'required|integer',
        ]);

        foreach ($request->order as $item) {
            SangguniangBayanMember::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }
}