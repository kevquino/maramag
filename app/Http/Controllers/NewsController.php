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
        //     'published_at' => $validated['published_at'],
        //     'image_path' => $imagePath,
        //     'user_id' => auth()->id(),
        // ]);

        return redirect()->route('news')
            ->with('success', 'News article created successfully!');
    }
}