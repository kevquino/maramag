<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Activity;
use App\Models\User;
use App\Models\BidsAward;
use App\Models\FullDisclosure;
use App\Models\TourismPackage;
use App\Models\AwardsRecognition;
use App\Models\SangguniangBayanMember;
use App\Models\OrdinanceResolution;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        
        // UNIVERSAL PERMISSION CHECK: Only check database permissions
        if (!$user->hasPermission('news')) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to access news management.'
            ]);
        }

        $query = News::with('author')->latest();

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $request->search . '%');
        }

        // Apply status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Apply category filter
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        $news = $query->paginate(10);

        // Get badge counts for the sidebar/navigation
        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('News/Index', [
            'news' => $news,
            'filters' => $request->only(['search', 'status', 'category']),
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $user = auth()->user();
        
        // UNIVERSAL PERMISSION CHECK: Only check database permissions
        if (!$user->hasPermission('news')) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to create news articles.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('News/Create', [
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // UNIVERSAL PERMISSION CHECK: Only check database permissions
        if (!$user->hasPermission('news')) {
            return redirect()->route('news.index')->with('error', 'You do not have permission to create news articles.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category' => 'required|string',
            'status' => 'required|string|in:draft,published',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        // Create the news article with a default slug
        $news = News::create([
            'title' => $validated['title'],
            'slug' => 'temp-slug-' . uniqid(), // Temporary slug
            'excerpt' => $validated['excerpt'] ?? '',
            'content' => $validated['content'],
            'category' => $validated['category'],
            'status' => $validated['status'],
            'is_featured' => $validated['is_featured'] ?? false,
            'published_at' => $validated['published_at'] ?? null,
            'author_id' => auth()->id(),
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('news-images', 'public');
            $news->update(['image_path' => $imagePath]);
        }

        // Log activity
        Activity::create([
            'description' => "New article created: {$news->title}",
            'type' => 'news',
            'user_id' => auth()->id(),
            'metadata' => [
                'news_id' => $news->id,
                'title' => $news->title,
                'action' => 'created'
            ]
        ]);

        return redirect()->route('news.index')->with('success', 'News created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news): Response
    {
        $user = auth()->user();
        
        // UNIVERSAL PERMISSION CHECK: Only check database permissions
        if (!$user->hasPermission('news')) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to view news articles.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('News/Show', [
            'news' => $news->load('author'),
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(News $news): Response
    {
        $user = auth()->user();
        
        // UNIVERSAL PERMISSION CHECK: Only check database permissions
        if (!$user->hasPermission('news')) {
            return Inertia::render('Unauthorized', [
                'message' => 'You do not have permission to edit news articles.'
            ]);
        }

        $badgeCounts = $this->getBadgeCounts($user);

        return Inertia::render('News/Edit', [
            'article' => $news->load('author'),
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, News $news)
    {
        $user = auth()->user();
        
        // UNIVERSAL PERMISSION CHECK: Only check database permissions
        if (!$user->hasPermission('news')) {
            return redirect()->route('news.index')->with('error', 'You do not have permission to update news articles.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category' => 'required|string',
            'status' => 'required|string|in:draft,published,archived',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'remove_existing_image' => 'boolean',
        ]);

        // Update the news article - include is_featured and published_at
        $news->update([
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'] ?? '',
            'content' => $validated['content'],
            'category' => $validated['category'],
            'status' => $validated['status'],
            'is_featured' => $validated['is_featured'] ?? false,
            'published_at' => $validated['published_at'] ?? null,
        ]);

        // Handle image upload/removal
        if ($request->boolean('remove_existing_image')) {
            // Remove existing image
            if ($news->image_path) {
                Storage::disk('public')->delete($news->image_path);
                $news->update(['image_path' => null]);
            }
        } elseif ($request->hasFile('image')) {
            // Remove old image if exists
            if ($news->image_path) {
                Storage::disk('public')->delete($news->image_path);
            }
            
            // Store new image
            $imagePath = $request->file('image')->store('news-images', 'public');
            $news->update(['image_path' => $imagePath]);
        }

        // Log activity
        Activity::create([
            'description' => "Article updated: {$news->title}",
            'type' => 'news',
            'user_id' => auth()->id(),
            'metadata' => [
                'news_id' => $news->id,
                'title' => $news->title,
                'action' => 'updated'
            ]
        ]);

        return redirect()->route('news.index')->with('success', 'News updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news)
    {
        $user = auth()->user();
        
        // UNIVERSAL PERMISSION CHECK: Only check database permissions
        if (!$user->hasPermission('news')) {
            return redirect()->route('news.index')->with('error', 'You do not have permission to delete news articles.');
        }

        $newsTitle = $news->title;
        
        // Delete image if exists
        if ($news->image_path) {
            Storage::disk('public')->delete($news->image_path);
        }
        
        $news->delete();

        // Log activity
        Activity::create([
            'description' => "Article deleted: {$newsTitle}",
            'type' => 'news',
            'user_id' => auth()->id(),
            'metadata' => [
                'news_id' => $news->id,
                'title' => $newsTitle,
                'action' => 'deleted'
            ]
        ]);

        return redirect()->route('news.index')->with('success', 'News deleted successfully.');
    }

    /**
     * Update news status.
     */
    public function updateStatus(Request $request, News $news)
    {
        $user = auth()->user();
        
        // UNIVERSAL PERMISSION CHECK: Only check database permissions
        if (!$user->hasPermission('news')) {
            return back()->with('error', 'You do not have permission to update news status.');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:draft,published',
        ]);

        $news->update(['status' => $validated['status']]);

        // Log activity
        Activity::create([
            'description' => "Article status changed to {$validated['status']}: {$news->title}",
            'type' => 'news',
            'user_id' => auth()->id(),
            'metadata' => [
                'news_id' => $news->id,
                'title' => $news->title,
                'action' => 'status_updated',
                'status' => $validated['status']
            ]
        ]);

        return back()->with('success', 'News status updated successfully.');
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(News $news)
    {
        $user = auth()->user();
        
        // UNIVERSAL PERMISSION CHECK: Only check database permissions
        if (!$user->hasPermission('news')) {
            return back()->with('error', 'You do not have permission to toggle featured status.');
        }

        $news->update(['is_featured' => !$news->is_featured]);

        $status = $news->is_featured ? 'featured' : 'unfeatured';

        // Log activity
        Activity::create([
            'description' => "Article {$status}: {$news->title}",
            'type' => 'news',
            'user_id' => auth()->id(),
            'metadata' => [
                'news_id' => $news->id,
                'title' => $news->title,
                'action' => 'featured_toggled',
                'is_featured' => $news->is_featured
            ]
        ]);

        return back()->with('success', 'News featured status updated successfully.');
    }

    /**
     * Get badge counts based on user permissions
     */
    private function getBadgeCounts(User $user): array
    {
        $badgeCounts = [];

        // UNIVERSAL PERMISSION SYSTEM: Only check database permissions
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

        return $badgeCounts;
    }
}