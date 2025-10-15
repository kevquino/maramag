<?php
// app/Http/Controllers/FullDisclosureController.php

namespace App\Http\Controllers;

use App\Models\FullDisclosure;
use App\Models\News;
use App\Models\Activity;
use App\Models\User;
use App\Models\BidsAward;
use App\Models\TourismPackage;
use App\Models\AwardsRecognition;
use App\Models\SangguniangBayanMember;
use App\Models\OrdinanceResolution;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class FullDisclosureController extends Controller
{
    public function index(Request $request): Response
    {
        $user = auth()->user();
        
        // Check if user has full_disclosure permission
        if (!$user->hasPermission('full_disclosure') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to access full disclosure management.'
            ]);
        }

        $categories = FullDisclosure::getCategories();
        
        $documents = FullDisclosure::with('user')
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Group by category for the frontend
        $groupedDocuments = FullDisclosure::with('user')
            ->where('is_published', true)
            ->get()
            ->groupBy('category');

        // Get badge counts for the sidebar/navigation
        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('FullDisclosure/Index', [
            'documents' => $documents,
            'groupedDocuments' => $groupedDocuments,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
            'badgeCounts' => $badgeCounts,
        ]);
    }

    public function create(): Response
    {
        $user = auth()->user();
        
        // Check if user has full_disclosure permission
        if (!$user->hasPermission('full_disclosure') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to create full disclosure documents.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('FullDisclosure/Create', [
            'categories' => FullDisclosure::getCategories(),
            'badgeCounts' => $badgeCounts,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Check if user has full_disclosure permission
        if (!$user->hasPermission('full_disclosure') && !$user->isAdmin()) {
            return redirect()->route('full-disclosure.index')->with('error', 'You do not have permission to create full disclosure documents.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', array_keys(FullDisclosure::getCategories())),
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $filePath = $file->store('full-disclosure', 'public');

        $fullDisclosure = FullDisclosure::create([
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $this->formatBytes($file->getSize()),
            'file_type' => $file->getClientMimeType(),
            'user_id' => Auth::id(),
            'is_published' => $request->boolean('is_published', true),
        ]);

        // Log activity
        Activity::create([
            'description' => "Full disclosure document created: {$fullDisclosure->title}",
            'type' => 'full_disclosure',
            'user_id' => auth()->id(),
            'metadata' => [
                'full_disclosure_id' => $fullDisclosure->id,
                'title' => $fullDisclosure->title,
                'category' => $fullDisclosure->category,
                'action' => 'created'
            ]
        ]);

        return redirect()->route('full-disclosure.index')
            ->with('success', 'Document uploaded successfully.');
    }

    public function show(FullDisclosure $fullDisclosure): Response
    {
        $user = auth()->user();
        
        // Check if user has full_disclosure permission
        if (!$user->hasPermission('full_disclosure') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to view full disclosure documents.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('FullDisclosure/Show', [
            'document' => $fullDisclosure->load('user'),
            'badgeCounts' => $badgeCounts,
        ]);
    }

    public function edit(FullDisclosure $fullDisclosure): Response
    {
        $user = auth()->user();
        
        // Check if user has full_disclosure permission
        if (!$user->hasPermission('full_disclosure') && !$user->isAdmin()) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to edit full disclosure documents.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('FullDisclosure/Edit', [
            'document' => $fullDisclosure,
            'categories' => FullDisclosure::getCategories(),
            'badgeCounts' => $badgeCounts,
        ]);
    }

    public function update(Request $request, FullDisclosure $fullDisclosure)
    {
        $user = auth()->user();
        
        // Check if user has full_disclosure permission
        if (!$user->hasPermission('full_disclosure') && !$user->isAdmin()) {
            return redirect()->route('full-disclosure.index')->with('error', 'You do not have permission to update full disclosure documents.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', array_keys(FullDisclosure::getCategories())),
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ]);

        $data = [
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
            'is_published' => $request->boolean('is_published', true),
        ];

        if ($request->hasFile('file')) {
            // Delete old file
            Storage::disk('public')->delete($fullDisclosure->file_path);

            $file = $request->file('file');
            $filePath = $file->store('full-disclosure', 'public');

            $data['file_path'] = $filePath;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $this->formatBytes($file->getSize());
            $data['file_type'] = $file->getClientMimeType();
        }

        $fullDisclosure->update($data);

        // Log activity
        Activity::create([
            'description' => "Full disclosure document updated: {$fullDisclosure->title}",
            'type' => 'full_disclosure',
            'user_id' => auth()->id(),
            'metadata' => [
                'full_disclosure_id' => $fullDisclosure->id,
                'title' => $fullDisclosure->title,
                'category' => $fullDisclosure->category,
                'action' => 'updated'
            ]
        ]);

        return redirect()->route('full-disclosure.index')
            ->with('success', 'Document updated successfully.');
    }

    public function destroy(FullDisclosure $fullDisclosure)
    {
        $user = auth()->user();
        
        // Check if user has full_disclosure permission
        if (!$user->hasPermission('full_disclosure') && !$user->isAdmin()) {
            return redirect()->route('full-disclosure.index')->with('error', 'You do not have permission to delete full disclosure documents.');
        }

        $documentTitle = $fullDisclosure->title;
        $documentCategory = $fullDisclosure->category;

        // Delete file from storage
        Storage::disk('public')->delete($fullDisclosure->file_path);
        
        $fullDisclosure->delete();

        // Log activity
        Activity::create([
            'description' => "Full disclosure document deleted: {$documentTitle}",
            'type' => 'full_disclosure',
            'user_id' => auth()->id(),
            'metadata' => [
                'full_disclosure_id' => $fullDisclosure->id,
                'title' => $documentTitle,
                'category' => $documentCategory,
                'action' => 'deleted'
            ]
        ]);

        return redirect()->route('full-disclosure.index')
            ->with('success', 'Document deleted successfully.');
    }

    public function download(FullDisclosure $fullDisclosure)
    {
        $user = auth()->user();
        
        // Check if user has full_disclosure permission
        if (!$user->hasPermission('full_disclosure') && !$user->isAdmin()) {
            return redirect()->route('full-disclosure.index')->with('error', 'You do not have permission to download this document.');
        }

        // Log download activity
        Activity::create([
            'description' => "Full disclosure document downloaded: {$fullDisclosure->title}",
            'type' => 'full_disclosure',
            'user_id' => auth()->id(),
            'metadata' => [
                'full_disclosure_id' => $fullDisclosure->id,
                'title' => $fullDisclosure->title,
                'category' => $fullDisclosure->category,
                'action' => 'downloaded'
            ]
        ]);

        return Storage::disk('public')->download($fullDisclosure->file_path, $fullDisclosure->file_name);
    }

    /**
     * Get badge counts based on user permissions
     */
    private function getBadgeCounts(User $user): array
    {
        $badgeCounts = [];

        // Debug: Check user permissions
        \Log::debug('FullDisclosureController - User permissions check', [
            'user_id' => $user->id,
            'has_full_disclosure_permission' => $user->hasPermission('full_disclosure'),
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
        \Log::debug('FullDisclosureController - Final badge counts', $badgeCounts);

        return $badgeCounts;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}