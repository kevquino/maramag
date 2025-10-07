<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Activity;
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

        return Inertia::render('News/Index', [
            'news' => $news,
            'filters' => $request->only(['search', 'status', 'category']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('News/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
        return Inertia::render('News/Show', [
            'news' => $news->load('author'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(News $news): Response
    {
        return Inertia::render('News/Edit', [
            'article' => $news->load('author'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category' => 'required|string',
            'status' => 'required|string|in:draft,published,archived',
            'is_featured' => 'boolean', // Added this line
            'published_at' => 'nullable|date', // Added this line
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
            'is_featured' => $validated['is_featured'] ?? false, // Added this line
            'published_at' => $validated['published_at'] ?? null, // Added this line
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
}