<?php
// app/Http/Controllers/TourismPackageController.php

namespace App\Http\Controllers;

use App\Models\TourismPackage;
use App\Models\News;
use App\Models\Activity;
use App\Models\User;
use App\Models\BidsAward;
use App\Models\FullDisclosure;
use App\Models\AwardsRecognition;
use App\Models\SangguniangBayanMember;
use App\Models\OrdinanceResolution;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TourismPackageController extends Controller
{
    public function index(Request $request): Response
    {
        $user = auth()->user();
        
        // Check if user has tourism permission
        if (!$user->hasPermission('tourism') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to access tourism management.'
            ]);
        }

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

        // Get badge counts for the sidebar/navigation
        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('Tourism/Index', [
            'packages' => $packages,
            'filters' => $filters,
            'categoryOptions' => $this->getCategoryOptions(),
            'difficultyOptions' => $this->getDifficultyOptions(),
            'statusOptions' => $this->getStatusOptions(),
            'badgeCounts' => $badgeCounts,
        ]);
    }

    public function create(): Response
    {
        $user = auth()->user();
        
        // Check if user has tourism permission
        if (!$user->hasPermission('tourism') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to create tourism packages.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('Tourism/Create', [
            'categoryOptions' => $this->getCategoryOptions(),
            'difficultyOptions' => $this->getDifficultyOptions(),
            'badgeCounts' => $badgeCounts,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Check if user has tourism permission
        if (!$user->hasPermission('tourism') && !$user->isAdmin()) {
            return redirect()->route('tourism.index')->with('error', 'You do not have permission to create tourism packages.');
        }

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

        $tourismPackage = TourismPackage::create($validated);

        // Log activity
        Activity::create([
            'description' => "Tourism package created: {$tourismPackage->title}",
            'type' => 'tourism',
            'user_id' => auth()->id(),
            'metadata' => [
                'tourism_package_id' => $tourismPackage->id,
                'title' => $tourismPackage->title,
                'category' => $tourismPackage->category,
                'action' => 'created'
            ]
        ]);

        return redirect()->route('tourism.index')
            ->with('success', 'Tourism Item created successfully.');
    }

    public function show(TourismPackage $tourismPackage): Response
    {
        $user = auth()->user();
        
        // Check if user has tourism permission
        if (!$user->hasPermission('tourism') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to view tourism packages.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('Tourism/Show', [
            'package' => $tourismPackage->load('user'),
            'badgeCounts' => $badgeCounts,
        ]);
    }

    public function edit(TourismPackage $tourismPackage): Response
    {
        $user = auth()->user();
        
        // Check if user has tourism permission
        if (!$user->hasPermission('tourism') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to edit tourism packages.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('Tourism/Edit', [
            'package' => $tourismPackage,
            'categoryOptions' => $this->getCategoryOptions(),
            'difficultyOptions' => $this->getDifficultyOptions(),
            'badgeCounts' => $badgeCounts,
        ]);
    }

    public function update(Request $request, TourismPackage $tourismPackage)
    {
        $user = auth()->user();
        
        // Check if user has tourism permission
        if (!$user->hasPermission('tourism') && !$user->isAdmin()) {
            return redirect()->route('tourism.index')->with('error', 'You do not have permission to update tourism packages.');
        }

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

        // Log activity
        Activity::create([
            'description' => "Tourism package updated: {$tourismPackage->title}",
            'type' => 'tourism',
            'user_id' => auth()->id(),
            'metadata' => [
                'tourism_package_id' => $tourismPackage->id,
                'title' => $tourismPackage->title,
                'category' => $tourismPackage->category,
                'action' => 'updated'
            ]
        ]);

        return redirect()->route('tourism.index')
            ->with('success', 'Tourism item updated successfully.');
    }

    public function destroy(TourismPackage $tourismPackage)
    {
        $user = auth()->user();
        
        // Check if user has tourism permission
        if (!$user->hasPermission('tourism') && !$user->isAdmin()) {
            return redirect()->route('tourism.index')->with('error', 'You do not have permission to delete tourism packages.');
        }

        $packageTitle = $tourismPackage->title;
        $packageCategory = $tourismPackage->category;

        $tourismPackage->delete();

        // Log activity
        Activity::create([
            'description' => "Tourism package deleted: {$packageTitle}",
            'type' => 'tourism',
            'user_id' => auth()->id(),
            'metadata' => [
                'tourism_package_id' => $tourismPackage->id,
                'title' => $packageTitle,
                'category' => $packageCategory,
                'action' => 'deleted'
            ]
        ]);

        return redirect()->route('tourism.index')
            ->with('success', 'Tourism item deleted successfully.');
    }

    public function toggleFeatured(TourismPackage $tourismPackage)
    {
        $user = auth()->user();
        
        // Check if user has tourism permission
        if (!$user->hasPermission('tourism') && !$user->isAdmin()) {
            return redirect()->route('tourism.index')->with('error', 'You do not have permission to toggle featured status.');
        }

        $tourismPackage->update([
            'is_featured' => !$tourismPackage->is_featured
        ]);

        $status = $tourismPackage->is_featured ? 'featured' : 'unfeatured';

        // Log activity
        Activity::create([
            'description' => "Tourism package {$status}: {$tourismPackage->title}",
            'type' => 'tourism',
            'user_id' => auth()->id(),
            'metadata' => [
                'tourism_package_id' => $tourismPackage->id,
                'title' => $tourismPackage->title,
                'action' => 'featured_toggled',
                'is_featured' => $tourismPackage->is_featured
            ]
        ]);

        return redirect()->back()
            ->with('success', 'Tourism item featured status updated successfully.');
    }

    public function toggleStatus(TourismPackage $tourismPackage)
    {
        $user = auth()->user();
        
        // Check if user has tourism permission
        if (!$user->hasPermission('tourism') && !$user->isAdmin()) {
            return redirect()->route('tourism.index')->with('error', 'You do not have permission to toggle status.');
        }

        $tourismPackage->update([
            'is_active' => !$tourismPackage->is_active
        ]);

        $status = $tourismPackage->is_active ? 'activated' : 'deactivated';

        // Log activity
        Activity::create([
            'description' => "Tourism package {$status}: {$tourismPackage->title}",
            'type' => 'tourism',
            'user_id' => auth()->id(),
            'metadata' => [
                'tourism_package_id' => $tourismPackage->id,
                'title' => $tourismPackage->title,
                'action' => 'status_toggled',
                'is_active' => $tourismPackage->is_active
            ]
        ]);

        return redirect()->back()
            ->with('success', 'Tourism item status updated successfully.');
    }

    /**
     * Get badge counts based on user permissions
     */
    private function getBadgeCounts(User $user): array
    {
        $badgeCounts = [];

        // Debug: Check user permissions
        \Log::debug('TourismPackageController - User permissions check', [
            'user_id' => $user->id,
            'has_tourism_permission' => $user->hasPermission('tourism'),
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
        \Log::debug('TourismPackageController - Final badge counts', $badgeCounts);

        return $badgeCounts;
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