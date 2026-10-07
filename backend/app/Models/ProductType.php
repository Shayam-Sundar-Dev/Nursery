<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'badge_color',
        'requires_botanical_attributes',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'requires_botanical_attributes' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Relationship to Products associated with this type.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'type', 'slug');
    }

    /**
     * Scope query to only active product types.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Generate an automated URL/key-friendly slug from name.
     */
    public static function generateUniqueSlug(string $name, ?int $exceptId = null): string
    {
        $baseSlug = Str::slug($name);
        // If Str::slug turns empty (e.g. symbols), default to 'type'
        if (empty($baseSlug)) {
            $baseSlug = 'type';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Color styling map for badge UI rendering.
     */
    public function getBadgeClassAttribute(): string
    {
        return match ($this->badge_color) {
            'emerald' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'amber' => 'bg-amber-100 text-amber-800 border-amber-200',
            'stone' => 'bg-stone-100 text-stone-800 border-stone-200',
            'blue' => 'bg-blue-100 text-blue-800 border-blue-200',
            'teal' => 'bg-teal-100 text-teal-800 border-teal-200',
            'purple' => 'bg-purple-100 text-purple-800 border-purple-200',
            'rose' => 'bg-rose-100 text-rose-800 border-rose-200',
            'indigo' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            default => 'bg-botanical-100 text-botanical-800 border-botanical-200',
        };
    }
}

