<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_amount',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_count',
        'per_user_limit',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
            'per_user_limit' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Check if coupon is valid for a given subtotal and optional customer.
     *
     * @return array{valid: bool, message: string}
     */
    public function validateForCart(float $subtotal, ?User $user = null): array
    {
        if (! $this->is_active) {
            return ['valid' => false, 'message' => "Coupon '{$this->code}' is currently inactive."];
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return ['valid' => false, 'message' => "Coupon '{$this->code}' is not valid until {$this->starts_at->format('M d, Y')}."];
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return ['valid' => false, 'message' => "Coupon '{$this->code}' expired on {$this->expires_at->format('M d, Y')}."];
        }

        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => "Coupon '{$this->code}' has reached its maximum total redemption limit."];
        }

        if ($this->min_order_amount > 0 && $subtotal < (float) $this->min_order_amount) {
            $formattedMin = number_format((float) $this->min_order_amount, 2);

            return ['valid' => false, 'message' => "Minimum order subtotal of ₹{$formattedMin} required for coupon '{$this->code}'."];
        }

        if ($user && $this->per_user_limit !== null) {
            $userUses = $this->orders()->where('user_id', $user->id)->count();
            if ($userUses >= $this->per_user_limit) {
                return ['valid' => false, 'message' => "You have already used coupon '{$this->code}' the maximum allowed times."];
            }
        }

        return ['valid' => true, 'message' => 'Coupon is valid.'];
    }

    /**
     * Calculate discount amount given cart subtotal and shipping fee.
     */
    public function calculateDiscount(float $subtotal, float $shippingFee = 0.00): float
    {
        $discount = 0.00;

        if ($this->discount_type === 'percentage') {
            $discount = ($subtotal * ((float) $this->discount_amount / 100));
            if ($this->max_discount_amount !== null && $discount > (float) $this->max_discount_amount) {
                $discount = (float) $this->max_discount_amount;
            }
        } elseif ($this->discount_type === 'fixed') {
            $discount = min($subtotal, (float) $this->discount_amount);
        } elseif ($this->discount_type === 'free_shipping') {
            $discount = $shippingFee;
        }

        return round(max(0.00, $discount), 2);
    }

    /**
     * Increment coupon usage count.
     */
    public function recordUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Get computed status string.
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

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return 'expired';
        }

        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return 'exhausted';
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
            'exhausted' => 'bg-amber-100 text-amber-800 border-amber-200',
            default => 'bg-stone-100 text-stone-600 border-stone-200',
        };
    }

    /**
     * Human-readable discount description.
     */
    public function discountLabel(): string
    {
        return match ($this->discount_type) {
            'percentage' => ((int) $this->discount_amount == $this->discount_amount ? (int) $this->discount_amount : $this->discount_amount).'% OFF',
            'fixed' => '₹'.number_format((float) $this->discount_amount, 2).' OFF',
            'free_shipping' => 'FREE SHIPPING',
        };
    }
}
