<?php

namespace App\Http\Controllers;

use App\Models\AwardsRecognition;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AwardsRecognitionController extends Controller
{
    // Display a listing of the awards
    public function index(Request $request)
    {
        $query = AwardsRecognition::with('user')
            ->latest();

        // Apply filters
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('awarding_body', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%");
            });
        }

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('award_type') && $request->award_type) {
            $query->where('award_type', $request->award_type);
        }

        if ($request->has('scope') && $request->scope) {
            $query->where('scope', $request->scope);
        }

        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $awards = $query->paginate(10)->withQueryString();

        $filters = $request->only(['search', 'category', 'award_type', 'scope', 'status']);

        return Inertia::render('AwardsRecognition/Index', [
            'awards' => $awards,
            'filters' => $filters,
            'categoryOptions' => $this->getCategoryOptions(),
            'awardTypeOptions' => $this->getAwardTypeOptions(),
            'scopeOptions' => $this->getScopeOptions(),
            'recipientTypeOptions' => $this->getRecipientTypeOptions(),
            'statusOptions' => $this->getStatusOptions(),
        ]);
    }

    // Show the form for creating a new award
    public function create()
    {
        return Inertia::render('AwardsRecognition/Create', [
            'categoryOptions' => $this->getCategoryOptions(),
            'awardTypeOptions' => $this->getAwardTypeOptions(),
            'scopeOptions' => $this->getScopeOptions(),
            'recipientTypeOptions' => $this->getRecipientTypeOptions(),
        ]);
    }

    // Store a newly created award
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'awarding_body' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'award_date' => 'required|date',
            'received_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'award_type' => 'required|string|max:255',
            'scope' => 'required|string|max:255',
            'significance' => 'nullable|string',
            'criteria' => 'nullable|string',
            'recipient_type' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpg,jpeg,png|max:5120',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $award = new AwardsRecognition($validated);
            $award->user_id = auth()->id();

            // Handle featured image upload
            if ($request->hasFile('featured_image')) {
                $path = $request->file('featured_image')->store('awards/featured-images', 'public');
                $award->featured_image = $path;
            }

            // Handle gallery images upload
            if ($request->hasFile('gallery_images')) {
                $galleryPaths = [];
                foreach ($request->file('gallery_images') as $image) {
                    $path = $image->store('awards/gallery-images', 'public');
                    $galleryPaths[] = $path;
                }
                $award->gallery_images = $galleryPaths;
            }

            // Handle supporting documents upload
            if ($request->hasFile('supporting_documents')) {
                $documentPaths = [];
                foreach ($request->file('supporting_documents') as $document) {
                    $path = $document->store('awards/supporting-documents', 'public');
                    $documentPaths[] = $path;
                }
                $award->supporting_documents = $documentPaths;
            }

            $award->save();
        });

        return redirect()->route('awards-recognition.index')
            ->with('success', 'Award created successfully.');
    }

    // Display the specified award
    public function show(AwardsRecognition $awardsRecognition)
    {
        $awardsRecognition->load('user');

        return Inertia::render('AwardsRecognition/Show', [
            'award' => $awardsRecognition,
        ]);
    }

    // Show the form for editing the specified award
    public function edit(AwardsRecognition $awardsRecognition)
    {
        return Inertia::render('AwardsRecognition/Edit', [
            'award' => $awardsRecognition,
            'categoryOptions' => $this->getCategoryOptions(),
            'awardTypeOptions' => $this->getAwardTypeOptions(),
            'scopeOptions' => $this->getScopeOptions(),
            'recipientTypeOptions' => $this->getRecipientTypeOptions(),
        ]);
    }

    // Update the specified award
    public function update(Request $request, AwardsRecognition $awardsRecognition)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'awarding_body' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'award_date' => 'required|date',
            'received_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'award_type' => 'required|string|max:255',
            'scope' => 'required|string|max:255',
            'significance' => 'nullable|string',
            'criteria' => 'nullable|string',
            'recipient_type' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpg,jpeg,png|max:5120',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $request, $awardsRecognition) {
            $awardsRecognition->fill($validated);

            // Handle featured image upload
            if ($request->hasFile('featured_image')) {
                // Delete old featured image
                if ($awardsRecognition->featured_image) {
                    Storage::disk('public')->delete($awardsRecognition->featured_image);
                }
                
                $path = $request->file('featured_image')->store('awards/featured-images', 'public');
                $awardsRecognition->featured_image = $path;
            }

            // Handle gallery images upload
            if ($request->hasFile('gallery_images')) {
                // Delete old gallery images
                if ($awardsRecognition->gallery_images) {
                    foreach ($awardsRecognition->gallery_images as $oldImage) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }
                
                $galleryPaths = [];
                foreach ($request->file('gallery_images') as $image) {
                    $path = $image->store('awards/gallery-images', 'public');
                    $galleryPaths[] = $path;
                }
                $awardsRecognition->gallery_images = $galleryPaths;
            }

            // Handle supporting documents upload
            if ($request->hasFile('supporting_documents')) {
                // Delete old supporting documents
                if ($awardsRecognition->supporting_documents) {
                    foreach ($awardsRecognition->supporting_documents as $oldDocument) {
                        Storage::disk('public')->delete($oldDocument);
                    }
                }
                
                $documentPaths = [];
                foreach ($request->file('supporting_documents') as $document) {
                    $path = $document->store('awards/supporting-documents', 'public');
                    $documentPaths[] = $path;
                }
                $awardsRecognition->supporting_documents = $documentPaths;
            }

            $awardsRecognition->save();
        });

        return redirect()->route('awards-recognition.index')
            ->with('success', 'Award updated successfully.');
    }

    // Remove the specified award
    public function destroy(AwardsRecognition $awardsRecognition)
    {
        DB::transaction(function () use ($awardsRecognition) {
            // Delete associated files
            if ($awardsRecognition->featured_image) {
                Storage::disk('public')->delete($awardsRecognition->featured_image);
            }

            if ($awardsRecognition->gallery_images) {
                foreach ($awardsRecognition->gallery_images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            if ($awardsRecognition->supporting_documents) {
                foreach ($awardsRecognition->supporting_documents as $document) {
                    Storage::disk('public')->delete($document);
                }
            }

            $awardsRecognition->delete();
        });

        return redirect()->route('awards-recognition.index')
            ->with('success', 'Award deleted successfully.');
    }

    // Toggle featured status
    public function toggleFeatured(AwardsRecognition $awardsRecognition)
    {
        $awardsRecognition->update([
            'is_featured' => !$awardsRecognition->is_featured
        ]);

        $status = $awardsRecognition->is_featured ? 'featured' : 'unfeatured';

        return redirect()->back()
            ->with('success', "Award {$status} successfully.");
    }

    // Toggle active status
    public function toggleStatus(AwardsRecognition $awardsRecognition)
    {
        $awardsRecognition->update([
            'is_active' => !$awardsRecognition->is_active
        ]);

        $status = $awardsRecognition->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Award {$status} successfully.");
    }

    // Helper methods for options
    private function getCategoryOptions()
    {
        return [
            'excellence' => 'Excellence Award',
            'innovation' => 'Innovation Award',
            'leadership' => 'Leadership Award',
            'service' => 'Service Award',
            'sustainability' => 'Sustainability Award',
            'quality' => 'Quality Award',
            'safety' => 'Safety Award',
            'community' => 'Community Service',
            'education' => 'Education Award',
            'health' => 'Health and Wellness',
            'environment' => 'Environmental Award',
            'tourism' => 'Tourism Award',
            'business' => 'Business Excellence',
            'technology' => 'Technology Innovation',
            'other' => 'Other',
        ];
    }

    private function getAwardTypeOptions()
    {
        return [
            'international' => 'International',
            'national' => 'National',
            'regional' => 'Regional',
            'provincial' => 'Provincial',
            'local' => 'Local/Municipal',
        ];
    }

    private function getScopeOptions()
    {
        return [
            'international' => 'International',
            'national' => 'National',
            'regional' => 'Regional',
            'provincial' => 'Provincial',
            'municipal' => 'Municipal',
        ];
    }

    private function getRecipientTypeOptions()
    {
        return [
            'individual' => 'Individual',
            'team' => 'Team',
            'department' => 'Department',
            'organization' => 'Organization',
        ];
    }

    private function getStatusOptions()
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ];
    }
}