<?php
// app/Models/FullDisclosure.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FullDisclosure extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'user_id',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Category constants
    const CATEGORY_APPROVED_BUDGET = 'approved_budget';
    const CATEGORY_PROCUREMENT_PLAN = 'procurement_plan';
    const CATEGORY_GENDER_DEVELOPMENT = 'gender_development';
    const CATEGORY_FULL_DISCLOSURE = 'full_disclosure_policy';
    const CATEGORY_AUDIT_REPORT = 'audit_report';
    const CATEGORY_EXECUTIVE_SUMMARY = 'executive_summary';
    const CATEGORY_STATEMENT_INDEBTEDNESS = 'statement_indebtedness';

    public static function getCategories(): array
    {
        return [
            self::CATEGORY_APPROVED_BUDGET => 'Approved Budget',
            self::CATEGORY_PROCUREMENT_PLAN => 'Annual Procurement Plan',
            self::CATEGORY_GENDER_DEVELOPMENT => 'Gender and Development',
            self::CATEGORY_FULL_DISCLOSURE => 'Full Disclosure Policy',
            self::CATEGORY_AUDIT_REPORT => 'AUDIT REPORT',
            self::CATEGORY_EXECUTIVE_SUMMARY => 'EXECUTIVE SUMMARY',
            self::CATEGORY_STATEMENT_INDEBTEDNESS => 'STATEMENT OF INDEBTEDNESS, PAYMENTS AND BALANCES',
        ];
    }

    public function getCategoryLabel(): string
    {
        return self::getCategories()[$this->category] ?? $this->category;
    }
}