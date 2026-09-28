<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'light_requirement',
        'watering_frequency',
        'difficulty_level',
        'pet_friendly',
        'air_purifying',
        'mature_size',
        'seasonality',
        'growth_rate',
        'ideal_temperature_range',
        'humidity_requirement',
        'pot_diameter_inches',
        'care_instructions',
    ];

    protected function casts(): array
    {
        return [
            'pet_friendly' => 'boolean',
            'air_purifying' => 'boolean',
            'pot_diameter_inches' => 'float',
            'care_instructions' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
