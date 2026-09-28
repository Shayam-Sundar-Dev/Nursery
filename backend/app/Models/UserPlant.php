<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPlant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'nickname',
        'adopted_at',
        'last_watered_at',
        'last_fertilized_at',
        'last_repotted_at',
        'reminder_frequency_days',
        'notes',
        'photo_url',
    ];

    protected function casts(): array
    {
        return [
            'adopted_at' => 'date',
            'last_watered_at' => 'datetime',
            'last_fertilized_at' => 'datetime',
            'last_repotted_at' => 'datetime',
            'reminder_frequency_days' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getNextWateringDueAttribute(): ?Carbon
    {
        if (! $this->last_watered_at) {
            return now();
        }

        return $this->last_watered_at->copy()->addDays($this->reminder_frequency_days);
    }

    public function isWateringOverdue(): bool
    {
        $due = $this->next_watering_due;

        return $due ? now()->greaterThan($due) : true;
    }
}
