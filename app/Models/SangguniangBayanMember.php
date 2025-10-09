<?php
// app/Models/SangguniangBayanMember.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class SangguniangBayanMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'position',
        'position_type',
        'bio',
        'email',
        'phone',
        'photo',
        'order',
        'is_active',
        'is_featured',
        'committees',
        'district',
        'term_start',
        'term_end',
    ];

    protected $casts = [
        'committees' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'order' => 'integer',
    ];

    // Position type constants
    const POSITION_REGULAR = 'regular';
    const POSITION_SK_PRESIDENT = 'sk_president';
    const POSITION_LIGA_PRESIDENT = 'liga_president';
    const POSITION_IP_REPRESENTATIVE = 'ip_representative';

    // Scope for active members
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for featured members
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Scope for ordering
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    // Get position types
    public static function getPositionTypes(): array
    {
        return [
            self::POSITION_REGULAR => 'Regular Member',
            self::POSITION_SK_PRESIDENT => 'SK President',
            self::POSITION_LIGA_PRESIDENT => 'Liga ng Barangay President',
            self::POSITION_IP_REPRESENTATIVE => 'IP Mandatory Representative',
        ];
    }

    // Accessor for photo URL
    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['photo'] 
                ? asset('storage/' . $attributes['photo']) 
                : asset('images/default-avatar.png'),
        );
    }

    // Check if member is special position
    public function isSpecialPosition(): bool
    {
        return in_array($this->position_type, [
            self::POSITION_SK_PRESIDENT,
            self::POSITION_LIGA_PRESIDENT,
            self::POSITION_IP_REPRESENTATIVE,
        ]);
    }
}