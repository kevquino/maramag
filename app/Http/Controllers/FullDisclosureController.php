<?php
// app/Http/Controllers/FullDisclosureController.php

namespace App\Http\Controllers;

use App\Models\FullDisclosure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class FullDisclosureController extends Controller
{
    public function index(Request $request)
    {
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

        return Inertia::render('FullDisclosure/Index', [
            'documents' => $documents,
            'groupedDocuments' => $groupedDocuments,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function create()
    {
        return Inertia::render('FullDisclosure/Create', [
            'categories' => FullDisclosure::getCategories(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', array_keys(FullDisclosure::getCategories())),
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $filePath = $file->store('full-disclosure', 'public');

        FullDisclosure::create([
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

        return redirect()->route('full-disclosure.index')
            ->with('success', 'Document uploaded successfully.');
    }

    public function show(FullDisclosure $fullDisclosure)
    {
        return Inertia::render('FullDisclosure/Show', [
            'document' => $fullDisclosure->load('user'),
        ]);
    }

    public function edit(FullDisclosure $fullDisclosure)
    {
        return Inertia::render('FullDisclosure/Edit', [
            'document' => $fullDisclosure,
            'categories' => FullDisclosure::getCategories(),
        ]);
    }

    public function update(Request $request, FullDisclosure $fullDisclosure)
    {
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

        return redirect()->route('full-disclosure.index')
            ->with('success', 'Document updated successfully.');
    }

    public function destroy(FullDisclosure $fullDisclosure)
    {
        // Delete file from storage
        Storage::disk('public')->delete($fullDisclosure->file_path);
        
        $fullDisclosure->delete();

        return redirect()->route('full-disclosure.index')
            ->with('success', 'Document deleted successfully.');
    }

    public function download(FullDisclosure $fullDisclosure)
    {
        return Storage::disk('public')->download($fullDisclosure->file_path, $fullDisclosure->file_name);
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