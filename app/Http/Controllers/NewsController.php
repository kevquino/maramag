<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        // Only users who can manage news can access the index page
        if (!auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized action.');
        }

        $news = News::with('author')
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('excerpt', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->category, function ($query, $category) {
                $query->where('category', 'like', "%{$category}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('News/Index', [
            'news' => $news,
            'filters' => $request->only(['search', 'status', 'category']),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function create()
    {
        if (!auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('News/Create', [
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
            'status' => 'required|in:draft,published,archived',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below.');
        }

        $validated = $validator->validated();

        try {
            $news = News::create([
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']),
                'excerpt' => $validated['excerpt'] ?? null,
                'content' => $validated['content'],
                'category' => $validated['category'],
                'status' => $validated['status'],
                'author_id' => auth()->id(),
                'published_at' => $validated['status'] === 'published' ? now() : null,
                'is_featured' => false,
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('news-images', 'public');
                $news->update([
                    'image_path' => $imagePath
                ]);
            }

            return redirect()->route('news.index')
                ->with('success', 'Article created successfully!');

        } catch (\Exception $e) {
            \Log::error('News creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create article. Please try again.');
        }
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
        if (!auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized action.');
        }

        // PIO Staff can only edit their own articles, Admins and PIO Officers can edit all
        if (auth()->user()->isPioStaff() && $news->author_id !== auth()->id()) {
            abort(403, 'You can only edit your own articles.');
        }

        $news->load('author');

        return Inertia::render('News/Edit', [
            'article' => $news,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function update(Request $request, News $news)
    {
        if (!auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized action.');
        }

        // PIO Staff can only update their own articles
        if (auth()->user()->isPioStaff() && $news->author_id !== auth()->id()) {
            abort(403, 'You can only update your own articles.');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
            'status' => 'required|in:draft,published,archived',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_existing_image' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below.');
        }

        $validated = $validator->validated();

        try {
            $updateData = [
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']),
                'excerpt' => $validated['excerpt'] ?? null,
                'content' => $validated['content'],
                'category' => $validated['category'],
                'status' => $validated['status'],
                'published_at' => $validated['status'] === 'published' 
                    ? ($news->published_at ?? now()) 
                    : null,
            ];

            // Handle image removal
            if ($request->boolean('remove_existing_image') && $news->image_path) {
                Storage::disk('public')->delete($news->image_path);
                $updateData['image_path'] = null;
            }

            $news->update($updateData);

            // Handle new image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($news->image_path) {
                    Storage::disk('public')->delete($news->image_path);
                }
                
                $imagePath = $request->file('image')->store('news-images', 'public');
                $news->update(['image_path' => $imagePath]);
            }

            return redirect()->route('news.index')
                ->with('success', 'Article updated successfully!');

        } catch (\Exception $e) {
            \Log::error('News update failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update article. Please try again.');
        }
    }

    public function destroy(News $news)
    {
        if (!auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized action.');
        }

        // PIO Staff can only delete their own articles
        if (auth()->user()->isPioStaff() && $news->author_id !== auth()->id()) {
            abort(403, 'You can only delete your own articles.');
        }

        try {
            // Delete image if exists
            if ($news->image_path) {
                Storage::disk('public')->delete($news->image_path);
            }
            
            $news->delete();

            return redirect()->route('news.index')
                ->with('success', 'Article deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('News deletion failed: ' . $e->getMessage());
            return redirect()->route('news.index')
                ->with('error', 'Failed to delete article. Please try again.');
        }
    }

    public function updateStatus(Request $request, News $news)
    {
        if (!auth()->user()->canManageNews()) {
            abort(403, 'Unauthorized action.');
        }

        // PIO Staff can only update status of their own articles
        if (auth()->user()->isPioStaff() && $news->author_id !== auth()->id()) {
            abort(403, 'You can only update the status of your own articles.');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,published,archived',
        ]);

        if ($validator->fails()) {
            return back()->with('error', 'Invalid status provided.');
        }

        $validated = $validator->validated();

        try {
            $news->update([
                'status' => $validated['status'],
                'published_at' => $validated['status'] === 'published' 
                    ? ($news->published_at ?? now()) 
                    : null,
            ]);

            return back()->with('success', 'Status updated successfully!');

        } catch (\Exception $e) {
            \Log::error('News status update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update status. Please try again.');
        }
    }

    public function toggleFeatured(Request $request, News $news)
    {
        // Only Admins and PIO Officers can feature articles (not PIO Staff)
        if (!auth()->user()->isAdmin() && !auth()->user()->isPioOfficer()) {
            abort(403, 'Unauthorized action. Only admins and PIO officers can feature articles.');
        }

        try {
            $news->update([
                'is_featured' => !$news->is_featured,
            ]);

            return back()->with('success', 'Feature status updated!');

        } catch (\Exception $e) {
            \Log::error('News feature toggle failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to toggle feature status. Please try again.');
        }
    }
}