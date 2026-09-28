<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeSlider extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'badge_text',
        'image_url',
        'mobile_image_url',
        'button_text',
        'button_link',
        'text_align',
        'theme',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? (str_starts_with($value, 'http') ? $value : asset($value)) : null,
        );
    }

    protected function mobileImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? (str_starts_with($value, 'http') ? $value : asset($value)) : null,
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc');
    }

    /**
     * Get computed status.
     */
    public function getStatusAttribute(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return 'scheduled';
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Badge classes for UI display.
     */
    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            'active' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'scheduled' => 'bg-blue-100 text-blue-800 border-blue-200',
            'expired' => 'bg-red-100 text-red-800 border-red-200',
            default => 'bg-stone-100 text-stone-600 border-stone-200',
        };
    }
}
