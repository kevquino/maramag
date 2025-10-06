<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage; // Add this import

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $news = News::with('author')
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('excerpt', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('News/Index', [
            'news' => $news,
            'filters' => $request->only(['search']),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('News/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
            'status' => 'required|in:draft,published,archived',
            'image' => 'nullable|image|max:2048',
        ]);

        $news = News::create([
            ...$validated,
            'slug' => Str::slug($validated['title']),
            'author_id' => auth()->id(),
            'published_at' => $validated['status'] === 'published' ? now() : null,
        ]);

        if ($request->hasFile('image')) {
            $news->update([
                'image_path' => $request->file('image')->store('news-images', 'public')
            ]);
        }

        return redirect()->route('news.index')->with('success', 'Article created successfully!');
    }

    public function show(News $news)
    {
        $news->load('author');
        
        return Inertia::render('News/Show', [
            'article' => $news,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function edit(News $news)
    {
        return Inertia::render('News/Edit', [
            'article' => $news,
        ]);
    }

    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
            'status' => 'required|in:draft,published,archived',
            'image' => 'nullable|image|max:2048',
        ]);

        $news->update([
            ...$validated,
            'slug' => Str::slug($validated['title']),
            'published_at' => $validated['status'] === 'published' ? ($news->published_at ?? now()) : null,
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($news->image_path) {
                Storage::disk('public')->delete($news->image_path);
            }
            
            $news->update([
                'image_path' => $request->file('image')->store('news-images', 'public')
            ]);
        }

        return redirect()->route('news.index')->with('success', 'Article updated successfully!');
    }

    public function destroy(News $news)
    {
        // Delete image if exists
        if ($news->image_path) {
            Storage::disk('public')->delete($news->image_path);
        }
        
        $news->delete();

        return redirect()->route('news.index')->with('success', 'Article deleted successfully!');
    }

    public function updateStatus(Request $request, News $news)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,published,archived',
        ]);

        $news->update([
            'status' => $validated['status'],
            'published_at' => $validated['status'] === 'published' ? ($news->published_at ?? now()) : null,
        ]);

        return back()->with('success', 'Status updated successfully!');
    }

    public function toggleFeatured(Request $request, News $news)
    {
        $news->update([
            'is_featured' => !$news->is_featured,
        ]);

        return back()->with('success', 'Feature status updated!');
    }
}