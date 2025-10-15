<?php
// app/Http/Controllers/OrdinanceResolutionController.php

namespace App\Http\Controllers;

use App\Models\OrdinanceResolution;
use App\Models\News;
use App\Models\Activity;
use App\Models\User;
use App\Models\BidsAward;
use App\Models\FullDisclosure;
use App\Models\TourismPackage;
use App\Models\AwardsRecognition;
use App\Models\SangguniangBayanMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Inertia\Response;

class OrdinanceResolutionController extends Controller
{
    // Define option arrays
    private $typeOptions = [
        'ordinance' => 'Ordinance',
        'resolution' => 'Resolution',
    ];

    private $statusOptions = [
        'active' => 'Active',
        'amended' => 'Amended',
        'repealed' => 'Repealed',
        'pending' => 'Pending',
    ];

    private $categoryOptions = [
        'revenue' => 'Revenue & Taxation',
        'appropriation' => 'Appropriation',
        'administrative' => 'Administrative',
        'development' => 'Development & Planning',
        'environment' => 'Environment & Sanitation',
        'peace_order' => 'Peace & Order',
        'health' => 'Health & Social Services',
        'education' => 'Education & Culture',
        'infrastructure' => 'Infrastructure & Public Works',
        'agriculture' => 'Agriculture & Fisheries',
        'business' => 'Business & Trade',
        'traffic' => 'Traffic & Transportation',
        'zoning' => 'Zoning & Land Use',
        'personnel' => 'Personnel & HR',
        'other' => 'Other',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        
        // Check if user has ordinance_resolutions permission
        if (!$user->hasPermission('ordinance_resolutions') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to access ordinances & resolutions management.'
            ]);
        }

        $query = OrdinanceResolution::with('user')
            ->search($request->search)
            ->byType($request->type)
            ->byStatus($request->status)
            ->byCategory($request->category)
            ->latest();

        $ordinanceResolutions = $query->paginate($request->per_page ?? 10);

        // Get badge counts for the sidebar/navigation
        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('OrdinanceResolution/Index', [
            'ordinanceResolutions' => $ordinanceResolutions,
            'filters' => $request->only(['search', 'type', 'status', 'category']),
            'typeOptions' => $this->typeOptions,
            'statusOptions' => $this->statusOptions,
            'categoryOptions' => $this->categoryOptions,
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $user = auth()->user();
        
        // Check if user has ordinance_resolutions permission
        if (!$user->hasPermission('ordinance_resolutions') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to create ordinances & resolutions.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('OrdinanceResolution/Create', [
            'typeOptions' => $this->typeOptions,
            'statusOptions' => $this->statusOptions,
            'categoryOptions' => $this->categoryOptions,
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Check if user has ordinance_resolutions permission
        if (!$user->hasPermission('ordinance_resolutions') && !$user->isAdmin()) {
            return redirect()->route('ordinance-resolutions.index')->with('error', 'You do not have permission to create ordinances & resolutions.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'number' => 'required|string|max:100|unique:ordinance_resolutions,number',
            'type' => 'required|in:ordinance,resolution',
            'description' => 'nullable|string',
            'date_approved' => 'required|date',
            'date_effectivity' => 'nullable|date',
            'sponsor' => 'nullable|string|max:255',
            'co_sponsors' => 'nullable|array',
            'co_sponsors.*' => 'string|max:255',
            'status' => 'required|in:active,amended,repealed,pending',
            'amendatory_to' => 'nullable|array',
            'amendatory_to.*' => 'string|max:255',
            'repealed_by' => 'nullable|array',
            'repealed_by.*' => 'string|max:255',
            'categories' => 'nullable|array',
            'categories.*' => 'in:' . implode(',', array_keys($this->categoryOptions)),
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB max
        ]);

        try {
            $filePath = null;
            $fileSize = null;
            $fileType = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . time() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('ordinance-resolutions', $fileName, 'public');
                $fileSize = $file->getSize();
                $fileType = $file->getMimeType();
            }

            $ordinanceResolution = OrdinanceResolution::create([
                ...$validated,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'file_type' => $fileType,
                'user_id' => auth()->id(),
            ]);

            // Log activity
            Activity::create([
                'description' => "Ordinance/Resolution created: {$ordinanceResolution->number} - {$ordinanceResolution->title}",
                'type' => 'ordinance_resolutions',
                'user_id' => auth()->id(),
                'metadata' => [
                    'ordinance_resolution_id' => $ordinanceResolution->id,
                    'number' => $ordinanceResolution->number,
                    'title' => $ordinanceResolution->title,
                    'type' => $ordinanceResolution->type,
                    'action' => 'created'
                ]
            ]);

            return redirect()->route('ordinance-resolutions.index')
                ->with('success', 'Ordinance/Resolution created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create ordinance/resolution: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(OrdinanceResolution $ordinanceResolution): Response
    {
        $user = auth()->user();
        
        // Check if user has ordinance_resolutions permission
        if (!$user->hasPermission('ordinance_resolutions') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to view ordinances & resolutions.'
            ]);
        }

        $ordinanceResolution->load('user');

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('OrdinanceResolution/Show', [
            'ordinanceResolution' => $ordinanceResolution,
            'typeOptions' => $this->typeOptions,
            'statusOptions' => $this->statusOptions,
            'categoryOptions' => $this->categoryOptions,
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrdinanceResolution $ordinanceResolution): Response
    {
        $user = auth()->user();
        
        // Check if user has ordinance_resolutions permission
        if (!$user->hasPermission('ordinance_resolutions') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to edit ordinances & resolutions.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('OrdinanceResolution/Edit', [
            'ordinanceResolution' => $ordinanceResolution,
            'typeOptions' => $this->typeOptions,
            'statusOptions' => $this->statusOptions,
            'categoryOptions' => $this->categoryOptions,
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrdinanceResolution $ordinanceResolution)
    {
        $user = auth()->user();
        
        // Check if user has ordinance_resolutions permission
        if (!$user->hasPermission('ordinance_resolutions') && !$user->isAdmin()) {
            return redirect()->route('ordinance-resolutions.index')->with('error', 'You do not have permission to update ordinances & resolutions.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'number' => 'required|string|max:100|unique:ordinance_resolutions,number,' . $ordinanceResolution->id,
            'type' => 'required|in:ordinance,resolution',
            'description' => 'nullable|string',
            'date_approved' => 'required|date',
            'date_effectivity' => 'nullable|date',
            'sponsor' => 'nullable|string|max:255',
            'co_sponsors' => 'nullable|array',
            'co_sponsors.*' => 'string|max:255',
            'status' => 'required|in:active,amended,repealed,pending',
            'amendatory_to' => 'nullable|array',
            'amendatory_to.*' => 'string|max:255',
            'repealed_by' => 'nullable|array',
            'repealed_by.*' => 'string|max:255',
            'categories' => 'nullable|array',
            'categories.*' => 'in:' . implode(',', array_keys($this->categoryOptions)),
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB max
        ]);

        try {
            $filePath = $ordinanceResolution->file_path;
            $fileSize = $ordinanceResolution->file_size;
            $fileType = $ordinanceResolution->file_type;

            if ($request->hasFile('file')) {
                // Delete old file if exists
                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }

                $file = $request->file('file');
                $fileName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . time() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('ordinance-resolutions', $fileName, 'public');
                $fileSize = $file->getSize();
                $fileType = $file->getMimeType();
            }

            $ordinanceResolution->update([
                ...$validated,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'file_type' => $fileType,
            ]);

            // Log activity
            Activity::create([
                'description' => "Ordinance/Resolution updated: {$ordinanceResolution->number} - {$ordinanceResolution->title}",
                'type' => 'ordinance_resolutions',
                'user_id' => auth()->id(),
                'metadata' => [
                    'ordinance_resolution_id' => $ordinanceResolution->id,
                    'number' => $ordinanceResolution->number,
                    'title' => $ordinanceResolution->title,
                    'type' => $ordinanceResolution->type,
                    'action' => 'updated'
                ]
            ]);

            return redirect()->route('ordinance-resolutions.index')
                ->with('success', 'Ordinance/Resolution updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update ordinance/resolution: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrdinanceResolution $ordinanceResolution)
    {
        $user = auth()->user();
        
        // Check if user has ordinance_resolutions permission
        if (!$user->hasPermission('ordinance_resolutions') && !$user->isAdmin()) {
            return redirect()->route('ordinance-resolutions.index')->with('error', 'You do not have permission to delete ordinances & resolutions.');
        }

        try {
            $ordinanceNumber = $ordinanceResolution->number;
            $ordinanceTitle = $ordinanceResolution->title;

            // Delete associated file
            if ($ordinanceResolution->file_path && Storage::disk('public')->exists($ordinanceResolution->file_path)) {
                Storage::disk('public')->delete($ordinanceResolution->file_path);
            }

            $ordinanceResolution->delete();

            // Log activity
            Activity::create([
                'description' => "Ordinance/Resolution deleted: {$ordinanceNumber} - {$ordinanceTitle}",
                'type' => 'ordinance_resolutions',
                'user_id' => auth()->id(),
                'metadata' => [
                    'ordinance_resolution_id' => $ordinanceResolution->id,
                    'number' => $ordinanceNumber,
                    'title' => $ordinanceTitle,
                    'action' => 'deleted'
                ]
            ]);

            return redirect()->route('ordinance-resolutions.index')
                ->with('success', 'Ordinance/Resolution deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete ordinance/resolution: ' . $e->getMessage());
        }
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(OrdinanceResolution $ordinanceResolution)
    {
        $user = auth()->user();
        
        // Check if user has ordinance_resolutions permission
        if (!$user->hasPermission('ordinance_resolutions') && !$user->isAdmin()) {
            return redirect()->route('ordinance-resolutions.index')->with('error', 'You do not have permission to toggle featured status.');
        }

        try {
            $ordinanceResolution->update([
                'is_featured' => !$ordinanceResolution->is_featured
            ]);

            $status = $ordinanceResolution->is_featured ? 'featured' : 'unfeatured';

            // Log activity
            Activity::create([
                'description' => "Ordinance/Resolution {$status}: {$ordinanceResolution->number} - {$ordinanceResolution->title}",
                'type' => 'ordinance_resolutions',
                'user_id' => auth()->id(),
                'metadata' => [
                    'ordinance_resolution_id' => $ordinanceResolution->id,
                    'number' => $ordinanceResolution->number,
                    'title' => $ordinanceResolution->title,
                    'action' => 'featured_toggled',
                    'is_featured' => $ordinanceResolution->is_featured
                ]
            ]);

            return back()->with('success', 'Featured status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update featured status: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(OrdinanceResolution $ordinanceResolution)
    {
        $user = auth()->user();
        
        // Check if user has ordinance_resolutions permission
        if (!$user->hasPermission('ordinance_resolutions') && !$user->isAdmin()) {
            return redirect()->route('ordinance-resolutions.index')->with('error', 'You do not have permission to toggle status.');
        }

        try {
            $ordinanceResolution->update([
                'is_active' => !$ordinanceResolution->is_active
            ]);

            $status = $ordinanceResolution->is_active ? 'activated' : 'deactivated';

            // Log activity
            Activity::create([
                'description' => "Ordinance/Resolution {$status}: {$ordinanceResolution->number} - {$ordinanceResolution->title}",
                'type' => 'ordinance_resolutions',
                'user_id' => auth()->id(),
                'metadata' => [
                    'ordinance_resolution_id' => $ordinanceResolution->id,
                    'number' => $ordinanceResolution->number,
                    'title' => $ordinanceResolution->title,
                    'action' => 'status_toggled',
                    'is_active' => $ordinanceResolution->is_active
                ]
            ]);

            return back()->with('success', 'Status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Download file
     */
    public function download(OrdinanceResolution $ordinanceResolution)
    {
        $user = auth()->user();
        
        // Check if user has ordinance_resolutions permission
        if (!$user->hasPermission('ordinance_resolutions') && !$user->isAdmin()) {
            return redirect()->route('ordinance-resolutions.index')->with('error', 'You do not have permission to download this file.');
        }

        if (!$ordinanceResolution->file_path || !Storage::disk('public')->exists($ordinanceResolution->file_path)) {
            return back()->with('error', 'File not found.');
        }

        // Log download activity
        Activity::create([
            'description' => "Ordinance/Resolution downloaded: {$ordinanceResolution->number} - {$ordinanceResolution->title}",
            'type' => 'ordinance_resolutions',
            'user_id' => auth()->id(),
            'metadata' => [
                'ordinance_resolution_id' => $ordinanceResolution->id,
                'number' => $ordinanceResolution->number,
                'title' => $ordinanceResolution->title,
                'action' => 'downloaded'
            ]
        ]);

        return Storage::disk('public')->download($ordinanceResolution->file_path, $ordinanceResolution->number . '.' . pathinfo($ordinanceResolution->file_path, PATHINFO_EXTENSION));
    }

    /**
     * Get badge counts based on user permissions
     */
    private function getBadgeCounts(User $user): array
    {
        $badgeCounts = [];

        // Debug: Check user permissions
        \Log::debug('OrdinanceResolutionController - User permissions check', [
            'user_id' => $user->id,
            'has_ordinance_resolutions_permission' => $user->hasPermission('ordinance_resolutions'),
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
        \Log::debug('OrdinanceResolutionController - Final badge counts', $badgeCounts);

        return $badgeCounts;
    }
}