<?php
// app/Models/BidsAward.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BidsAward extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'reference_number',
        'bid_type',
        'estimated_budget',
        'bid_opening_date',
        'bid_closing_date',
        'award_date',
        'status',
        'is_featured',
        'awarded_to',
        'awarded_amount',
        'award_remarks',
        'documents',
        'user_id'
    ];

    protected $casts = [
        'estimated_budget' => 'decimal:2',
        'awarded_amount' => 'decimal:2',
        'bid_opening_date' => 'date',
        'bid_closing_date' => 'date',
        'award_date' => 'date',
        'is_featured' => 'boolean',
        'documents' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope for active bids
    public function scopeActive($query)
    {
        return $query->where('status', 'published')
                    ->where('bid_closing_date', '>=', now());
    }

    // Scope for featured bids
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Scope for published bids
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}