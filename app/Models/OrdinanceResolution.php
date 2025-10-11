<?php
// app/Models/OrdinanceResolution.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class OrdinanceResolution extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'number',
        'type',
        'description',
        'date_approved',
        'date_effectivity',
        'sponsor',
        'co_sponsors',
        'status',
        'amendatory_to',
        'repealed_by',
        'categories',
        'file_path',
        'file_size',
        'file_type',
        'is_featured',
        'is_active',
        'user_id',
    ];

    protected $casts = [
        'categories' => 'array',
        'co_sponsors' => 'array',
        'amendatory_to' => 'array',
        'repealed_by' => 'array',
        'date_approved' => 'date',
        'date_effectivity' => 'date',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $appends = ['file_url'];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    protected function fileUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->file_path ? asset('storage/' . $this->file_path) : null,
        );
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

    public function scopeOrdinances($query)
    {
        return $query->where('type', 'ordinance');
    }

    public function scopeResolutions($query)
    {
        return $query->where('type', 'resolution');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('number', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('sponsor', 'like', "%{$search}%");
        });
    }

    public function scopeByType($query, $type)
    {
        if ($type) {
            return $query->where('type', $type);
        }
        return $query;
    }

    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeByCategory($query, $category)
    {
        if ($category) {
            return $query->whereJsonContains('categories', $category);
        }
        return $query;
    }
}