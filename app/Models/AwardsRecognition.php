<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AwardsRecognition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'awarding_body',
        'category',
        'award_date',
        'received_date',
        'location',
        'award_type',
        'scope',
        'significance',
        'criteria',
        'recipient_type',
        'recipient_name',
        'contact_person',
        'contact_email',
        'contact_phone',
        'supporting_documents',
        'featured_image',
        'gallery_images',
        'is_featured',
        'is_active',
        'user_id',
    ];

    protected $casts = [
        'award_date' => 'date',
        'received_date' => 'date',
        'supporting_documents' => 'array',
        'gallery_images' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByAwardType($query, $awardType)
    {
        return $query->where('award_type', $awardType);
    }

    public function scopeByScope($query, $scope)
    {
        return $query->where('scope', $scope);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('award_date', '>=', now()->subDays($days));
    }

    // Accessors
    public function getFeaturedImageUrlAttribute()
    {
        return $this->featured_image ? asset('storage/' . $this->featured_image) : null;
    }

    public function getGalleryImagesUrlsAttribute()
    {
        if (!$this->gallery_images) {
            return [];
        }

        return array_map(function ($image) {
            return asset('storage/' . $image);
        }, $this->gallery_images);
    }

    public function getSupportingDocumentsUrlsAttribute()
    {
        if (!$this->supporting_documents) {
            return [];
        }

        return array_map(function ($document) {
            return asset('storage/' . $document);
        }, $this->supporting_documents);
    }

    // Helper methods
    public function isRecent()
    {
        return $this->award_date->gte(now()->subMonths(6));
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    public function getFeaturedBadgeAttribute()
    {
        return $this->is_featured ? 'Featured' : null;
    }
}