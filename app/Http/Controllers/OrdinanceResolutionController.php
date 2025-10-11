<?php
// app/Http/Controllers/OrdinanceResolutionController.php

namespace App\Http\Controllers;

use App\Models\OrdinanceResolution;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Str;

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
    public function index(Request $request)
    {
        $query = OrdinanceResolution::with('user')
            ->search($request->search)
            ->byType($request->type)
            ->byStatus($request->status)
            ->byCategory($request->category)
            ->latest();

        $ordinanceResolutions = $query->paginate($request->per_page ?? 10);

        return Inertia::render('OrdinanceResolution/Index', [
            'ordinanceResolutions' => $ordinanceResolutions,
            'filters' => $request->only(['search', 'type', 'status', 'category']),
            'typeOptions' => $this->typeOptions,
            'statusOptions' => $this->statusOptions,
            'categoryOptions' => $this->categoryOptions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('OrdinanceResolution/Create', [
            'typeOptions' => $this->typeOptions,
            'statusOptions' => $this->statusOptions,
            'categoryOptions' => $this->categoryOptions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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

            return redirect()->route('ordinance-resolutions.index')
                ->with('success', 'Ordinance/Resolution created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create ordinance/resolution: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(OrdinanceResolution $ordinanceResolution)
    {
        $ordinanceResolution->load('user');

        return Inertia::render('OrdinanceResolution/Show', [
            'ordinanceResolution' => $ordinanceResolution,
            'typeOptions' => $this->typeOptions,
            'statusOptions' => $this->statusOptions,
            'categoryOptions' => $this->categoryOptions,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrdinanceResolution $ordinanceResolution)
    {
        return Inertia::render('OrdinanceResolution/Edit', [
            'ordinanceResolution' => $ordinanceResolution,
            'typeOptions' => $this->typeOptions,
            'statusOptions' => $this->statusOptions,
            'categoryOptions' => $this->categoryOptions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrdinanceResolution $ordinanceResolution)
    {
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
        try {
            // Delete associated file
            if ($ordinanceResolution->file_path && Storage::disk('public')->exists($ordinanceResolution->file_path)) {
                Storage::disk('public')->delete($ordinanceResolution->file_path);
            }

            $ordinanceResolution->delete();

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
        try {
            $ordinanceResolution->update([
                'is_featured' => !$ordinanceResolution->is_featured
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
        try {
            $ordinanceResolution->update([
                'is_active' => !$ordinanceResolution->is_active
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
        if (!$ordinanceResolution->file_path || !Storage::disk('public')->exists($ordinanceResolution->file_path)) {
            return back()->with('error', 'File not found.');
        }

        return Storage::disk('public')->download($ordinanceResolution->file_path, $ordinanceResolution->number . '.' . pathinfo($ordinanceResolution->file_path, PATHINFO_EXTENSION));
    }
}