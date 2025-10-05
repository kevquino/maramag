<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function index()
    {
        return Inertia::render('News/Index');
    }

    public function create()
    {
        return Inertia::render('News/Create');
    }

    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|in:announcement,update,event,maintenance',
            'published_at' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('news-images', 'public');
        }

        // Here you would typically save to database
        // Example:
        // $news = News::create([
        //     'title' => $validated['title'],
        //     'content' => $validated['content'],
        //     'category' => $validated['category'],
        //     'published_at' => $validated['publishedAt'],
        //     'image_path' => $imagePath,
        //     'user_id' => auth()->id(),
        // ]);

        return redirect()->route('news')
            ->with('success', 'News article created successfully!');
    }

    public function show($id)
    {
        // In real application, you would fetch the article from database
        // $article = News::findOrFail($id);
        
        return Inertia::render('News/Show', [
            'id' => $id,
            // 'article' => $article // Pass the article data
        ]);
    }

    public function edit($id)
    {
        // In real application, you would fetch the article from database
        // $article = News::findOrFail($id);
        
        return Inertia::render('News/Edit', [
            'id' => $id,
            // 'article' => $article // Pass the article data
        ]);
    }

    public function update(Request $request, $id)
    {
        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string',
            'published_at' => 'nullable|date',
            'status' => 'required|in:published,draft,archived',
            'isFeatured' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('news-images', 'public');
            
            // Delete old image if exists
            // $oldArticle = News::find($id);
            // if ($oldArticle && $oldArticle->image_path) {
            //     Storage::disk('public')->delete($oldArticle->image_path);
            // }
        }

        // Here you would typically update the database
        // Example:
        // $news = News::findOrFail($id);
        // $news->update([
        //     'title' => $validated['title'],
        //     'content' => $validated['content'],
        //     'category' => $validated['category'],
        //     'published_at' => $validated['published_at'],
        //     'status' => $validated['status'],
        //     'is_featured' => $validated['isFeatured'],
        //     'image_path' => $imagePath ?: $news->image_path,
        // ]);

        return redirect()->route('news')
            ->with('success', 'News article updated successfully!');
    }

    public function destroy($id)
    {
        // In real application, you would delete the article from database
        // $article = News::findOrFail($id);
        // $article->delete();

        return redirect()->route('news')
            ->with('success', 'News article deleted successfully!');
    }
}