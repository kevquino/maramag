<?php
// app/Models/TourismPackage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TourismPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'location',
        'category',
        'price',
        'duration_days',
        'duration_nights',
        'inclusions',
        'exclusions',
        'itinerary',
        'difficulty_level',
        'max_participants',
        'is_featured',
        'is_active',
        'featured_image',
        'gallery_images',
        'contact_person',
        'contact_email',
        'contact_phone',
        'user_id',
    ];

    protected $casts = [
        'inclusions' => 'array',
        'exclusions' => 'array',
        'itinerary' => 'array',
        'gallery_images' => 'array',
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}