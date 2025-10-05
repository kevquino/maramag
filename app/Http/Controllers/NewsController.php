<?php

namespace App\Http\Controllers;

use App\Models\News;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        // Check if user can manage news
        if (!auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized access to news management.');
        }

        $query = News::with('author')
            ->latest();

        // Search filter
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Category filter
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $perPage = $request->get('per_page', 10);
        $news = $query->paginate($perPage);

        // Return JSON for AJAX requests only when explicitly requested
        if ($request->ajax() && $request->has('ajax')) {
            return response()->json([
                'data' => $news->items(),
                'total' => $news->total(),
                'current_page' => $news->currentPage(),
                'per_page' => $news->perPage(),
                'last_page' => $news->lastPage(),
            ]);
        }

        // Always return Inertia response for regular requests
        return Inertia::render('News/Index', [
            'news' => $news,
            'filters' => $request->only(['search', 'status', 'category']),
        ]);
    }

    // ... rest of your methods remain the same
    public function create()
    {
        if (!auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized access to news management.');
        }

        return Inertia::render('News/Create', [
            'categories' => [
                'Business', 'Finance', 'Events', 'Partnerships', 
                'Sustainability', 'Company News', 'Announcement', 
                'Update', 'Event', 'Maintenance'
            ],
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized access to news management.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category' => 'required|string',
            'published_at' => 'nullable|date',
            'status' => 'required|in:published,draft,archived',
            'is_featured' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('news-images', 'public');
        }

        // Generate slug from title
        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $counter = 1;
        
        while (News::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $news = News::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'published_at' => $validated['published_at'] ?? now(),
            'status' => $validated['status'],
            'is_featured' => $validated['is_featured'] ?? false,
            'image_path' => $imagePath,
            'author_id' => auth()->id(),
        ]);

        return redirect()->route('news.index')
            ->with('success', 'News article created successfully!');
    }

    public function show(News $news)
    {
        if (!auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized access to news management.');
        }

        $news->load('author');
        
        return Inertia::render('News/Show', [
            'article' => $news,
        ]);
    }

    public function edit(News $news)
    {
        if (!auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized access to news management.');
        }

        return Inertia::render('News/Edit', [
            'article' => $news->load('author'),
            'categories' => [
                'Business', 'Finance', 'Events', 'Partnerships', 
                'Sustainability', 'Company News', 'Announcement', 
                'Update', 'Event', 'Maintenance'
            ],
        ]);
    }

    public function update(Request $request, News $news)
    {
        if (!auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized access to news management.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category' => 'required|string',
            'published_at' => 'nullable|date',
            'status' => 'required|in:published,draft,archived',
            'is_featured' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        // Handle image upload
        $imagePath = $news->image_path;
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($news->image_path) {
                Storage::disk('public')->delete($news->image_path);
            }
            $imagePath = $request->file('image')->store('news-images', 'public');
        }

        // Update slug if title changed
        $slug = $news->slug;
        if ($news->title !== $request->title) {
            $slug = Str::slug($request->title);
            $originalSlug = $slug;
            $counter = 1;
            
            while (News::where('slug', $slug)->where('id', '!=', $news->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $news->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'published_at' => $validated['published_at'] ?? $news->published_at,
            'status' => $validated['status'],
            'is_featured' => $validated['is_featured'] ?? false,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('news.index')
            ->with('success', 'News article updated successfully!');
    }

    public function destroy(News $news)
    {
        if (!auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized access to news management.');
        }

        // Delete associated image
        if ($news->image_path) {
            Storage::disk('public')->delete($news->image_path);
        }

        $news->delete();

        return redirect()->route('news.index')
            ->with('success', 'News article deleted successfully!');
    }

    public function updateStatus(Request $request, News $news)
    {
        if (!auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized access to news management.');
        }

        $request->validate([
            'status' => 'required|in:published,draft,archived',
        ]);

        $updateData = ['status' => $request->status];
        
        // Set published_at when publishing
        if ($request->status === 'published' && !$news->published_at) {
            $updateData['published_at'] = now();
        }

        $news->update($updateData);

        return response()->json(['success' => 'Article status updated successfully!']);
    }

    public function toggleFeatured(News $news)
    {
        if (!auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized access to news management.');
        }

        $news->update([
            'is_featured' => !$news->is_featured,
        ]);

        return response()->json([
            'success' => 'Article featured status updated!',
            'is_featured' => $news->fresh()->is_featured
        ]);
    }
}