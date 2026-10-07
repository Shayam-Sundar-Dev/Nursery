<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'botanical_name',
        'slug',
        'type',
        'short_description',
        'description',
        'base_price',
        'primary_image_url',
        'gallery_images',
        'is_featured',
        'is_published',
        'requires_special_shipping',
    ];

    protected $appends = [
        'care_attribute',
        'default_variant',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'requires_special_shipping' => 'boolean',
        ];
    }

    protected function primaryImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? (str_starts_with($value, 'http') ? $value : asset($value)) : null,
        );
    }

    protected function galleryImages(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (empty($value)) {
                    return [];
                }
                $images = is_string($value) ? json_decode($value, true) : $value;
                if (! is_array($images)) {
                    return [];
                }

                return array_values(array_map(function ($img) {
                    if (! is_string($img)) {
                        return $img;
                    }

                    return str_starts_with($img, 'http') ? $img : asset($img);
                }, $images));
            },
            set: fn ($value) => is_array($value) ? json_encode(array_values($value)) : $value,
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'type', 'slug');
    }

    public function plantAttributes(): HasOne
    {
        return $this->hasOne(PlantAttribute::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function careAttribute(): Attribute
    {
        return Attribute::make(
            get: function () {
                $attrs = $this->plantAttributes;
                if (! $attrs) {
                    return null;
                }

                return [
                    'light_requirement' => $attrs->light_requirement,
                    'watering_frequency' => $attrs->watering_frequency,
                    'difficulty_level' => $attrs->difficulty_level,
                    'is_pet_friendly' => (bool) $attrs->pet_friendly,
                    'pet_friendly' => (bool) $attrs->pet_friendly,
                    'is_air_purifying' => (bool) $attrs->air_purifying,
                    'air_purifying' => (bool) $attrs->air_purifying,
                    'mature_size' => $attrs->mature_size,
                    'seasonality' => $attrs->seasonality,
                    'growth_rate' => $attrs->growth_rate,
                    'ideal_temperature_range' => $attrs->ideal_temperature_range,
                    'humidity_requirement' => $attrs->humidity_requirement,
                    'humidity_level' => $attrs->humidity_requirement,
                    'pot_diameter_inches' => $attrs->pot_diameter_inches,
                    'care_instructions' => $attrs->care_instructions,
                ];
            }
        );
    }

    public function defaultVariant(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->variants->first(),
        );
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeFilterBotanical(Builder $query, array $filters): Builder
    {
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Support category filtering by category_id or by slug/name (including subcategories)
        $category = $filters['category_id'] ?? $filters['category'] ?? null;
        if (! empty($category)) {
            $catModel = is_numeric($category)
                ? Category::find((int) $category)
                : Category::where('slug', $category)->orWhere('name', $category)->first();

            if ($catModel) {
                $categoryIds = Category::where('parent_id', $catModel->id)
                    ->pluck('id')
                    ->push($catModel->id)
                    ->all();
                $query->whereIn('category_id', $categoryIds);
            } else {
                $query->whereHas('category', function (Builder $q) use ($category) {
                    $q->where('slug', $category)->orWhere('name', $category);
                });
            }
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('botanical_name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        $rawLight = $filters['light'] ?? $filters['light_requirement'] ?? null;
        // Normalize slug-style values (e.g. "low-light") to DB proper-case ("Low Light")
        $lightNormalizeMap = [
            'low-light'        => 'Low Light',
            'bright-indirect'  => 'Bright Indirect',
            'direct-sun'       => 'Direct Sun',
            'full-sun'         => 'Full Sun',
        ];
        $light = $rawLight ? ($lightNormalizeMap[strtolower($rawLight)] ?? $rawLight) : null;

        $watering = $filters['watering'] ?? $filters['watering_frequency'] ?? null;
        $difficulty = $filters['difficulty'] ?? null;
        $hasPetFriendly = isset($filters['pet_friendly']) && $filters['pet_friendly'] !== '';
        $hasAirPurifying = isset($filters['air_purifying']) && $filters['air_purifying'] !== '';

        if (! empty($light) || ! empty($watering) || ! empty($difficulty) || $hasPetFriendly || $hasAirPurifying) {
            $query->whereHas('plantAttributes', function (Builder $q) use ($light, $watering, $difficulty, $filters, $hasPetFriendly, $hasAirPurifying) {
                if (! empty($light)) {
                    $q->where('light_requirement', 'like', "%{$light}%");
                }
                if (! empty($watering)) {
                    $q->where('watering_frequency', 'like', "%{$watering}%");
                }
                if (! empty($difficulty)) {
                    $q->where('difficulty_level', $difficulty);
                }
                if ($hasPetFriendly) {
                    $isPet = filter_var($filters['pet_friendly'], FILTER_VALIDATE_BOOLEAN);
                    if ($isPet) {
                        $q->where('pet_friendly', true);
                    }
                }
                if ($hasAirPurifying) {
                    $isAir = filter_var($filters['air_purifying'], FILTER_VALIDATE_BOOLEAN);
                    if ($isAir) {
                        $q->where('air_purifying', true);
                    }
                }
            });
        }

        return $query;
    }
}
