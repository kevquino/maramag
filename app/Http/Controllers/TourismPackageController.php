<?php
// app/Http/Controllers/TourismPackageController.php

namespace App\Http\Controllers;

use App\Models\TourismPackage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TourismPackageController extends Controller
{
    public function index(Request $request)
    {
        // Get filters from request
        $filters = $request->only(['search', 'category', 'status', 'difficulty']);
        
        // Build query
        $query = TourismPackage::with('user');
        
        // Apply search filter
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }
        
        // Apply category filter
        if ($request->category) {
            $query->where('category', $request->category);
        }
        
        // Apply status filter
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'featured') {
            $query->where('is_featured', true);
        }
        
        // Apply difficulty filter
        if ($request->difficulty) {
            $query->where('difficulty_level', $request->difficulty);
        }
        
        // Get paginated results
        $packages = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Tourism/Index', [
            'packages' => $packages,
            'filters' => $filters,
            'categoryOptions' => $this->getCategoryOptions(),
            'difficultyOptions' => $this->getDifficultyOptions(),
            'statusOptions' => $this->getStatusOptions(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Tourism/Create', [
            'categoryOptions' => $this->getCategoryOptions(),
            'difficultyOptions' => $this->getDifficultyOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'nullable|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'duration_nights' => 'required|integer|min:0',
            'difficulty_level' => 'required|string',
            'max_participants' => 'nullable|integer|min:1',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        $validated['user_id'] = auth()->id();

        TourismPackage::create($validated);

        return redirect()->route('tourism.index')
            ->with('success', 'Tourism Item created successfully.');
    }

    public function show(TourismPackage $tourismPackage)
    {
        return Inertia::render('Tourism/Show', [
            'package' => $tourismPackage->load('user'),
        ]);
    }

    public function edit(TourismPackage $tourismPackage)
    {
        return Inertia::render('Tourism/Edit', [
            'package' => $tourismPackage,
            'categoryOptions' => $this->getCategoryOptions(),
            'difficultyOptions' => $this->getDifficultyOptions(),
        ]);
    }

    public function update(Request $request, TourismPackage $tourismPackage)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'nullable|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'duration_nights' => 'required|integer|min:0',
            'difficulty_level' => 'required|string',
            'max_participants' => 'nullable|integer|min:1',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        $tourismPackage->update($validated);

        return redirect()->route('tourism.index')
            ->with('success', 'Tourism item updated successfully.');
    }

    public function destroy(TourismPackage $tourismPackage)
    {
        $tourismPackage->delete();

        return redirect()->route('tourism.index')
            ->with('success', 'Tourism item deleted successfully.');
    }

    public function toggleFeatured(TourismPackage $tourismPackage)
    {
        $tourismPackage->update([
            'is_featured' => !$tourismPackage->is_featured
        ]);

        return redirect()->back()
            ->with('success', 'Tourism item featured status updated successfully.');
    }

    public function toggleStatus(TourismPackage $tourismPackage)
    {
        $tourismPackage->update([
            'is_active' => !$tourismPackage->is_active
        ]);

        return redirect()->back()
            ->with('success', 'Tourism item status updated successfully.');
    }

    private function getCategoryOptions()
    {
        return [
            'adventure' => 'Adventure',
            'cultural' => 'Cultural',
            'beach' => 'Beach',
            'mountain' => 'Mountain',
            'eco_tourism' => 'Eco Tourism',
            'heritage' => 'Heritage',
            'food' => 'Food Tour',
            'wellness' => 'Wellness',
        ];
    }

    private function getDifficultyOptions()
    {
        return [
            'easy' => 'Easy',
            'moderate' => 'Moderate',
            'difficult' => 'Difficult',
        ];
    }

    private function getStatusOptions()
    {
        return [
            'active' => 'Active',
            'featured' => 'Featured',
        ];
    }
}