<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('site_settings_all');
        });

        static::deleted(function () {
            Cache::forget('site_settings_all');
        });
    }

    /**
     * Get a setting value with optional fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::allCached();

        if (! array_key_exists($key, $all)) {
            return $default;
        }

        return $all[$key]['value'] ?? $default;
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string', bool $isPublic = true): self
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->group = $group;
        $setting->type = $type;
        $setting->is_public = $isPublic;

        if (is_array($value) || is_object($value)) {
            $setting->value = json_encode($value);
            $setting->type = 'json';
        } elseif (is_bool($value)) {
            $setting->value = $value ? '1' : '0';
            $setting->type = 'boolean';
        } else {
            $setting->value = (string) $value;
        }

        $setting->save();
        Cache::forget('site_settings_all');

        return $setting;
    }

    /**
     * Set a private (non-public) setting — secrets never exposed via public API.
     */
    public static function setPrivate(string $key, mixed $value, string $group = 'general'): self
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->group = $group;
        $setting->type = 'string';
        $setting->is_public = false;
        $setting->value = (string) $value;
        $setting->save();
        Cache::forget('site_settings_all');
        return $setting;
    }

    /**
     * Default state delivery rate schedule for Indian states and union territories.
     */
    public static function defaultIndianStateRates(): array
    {
        return [
            ['state' => 'Karnataka', 'fee' => 49.00, 'estimated_days' => '1-2 days'],
            ['state' => 'Tamil Nadu', 'fee' => 69.00, 'estimated_days' => '2-3 days'],
            ['state' => 'Kerala', 'fee' => 69.00, 'estimated_days' => '2-3 days'],
            ['state' => 'Andhra Pradesh', 'fee' => 79.00, 'estimated_days' => '2-3 days'],
            ['state' => 'Telangana', 'fee' => 79.00, 'estimated_days' => '2-3 days'],
            ['state' => 'Goa', 'fee' => 69.00, 'estimated_days' => '2-3 days'],
            ['state' => 'Puducherry', 'fee' => 69.00, 'estimated_days' => '2-3 days'],
            ['state' => 'Maharashtra', 'fee' => 89.00, 'estimated_days' => '3-4 days'],
            ['state' => 'Gujarat', 'fee' => 99.00, 'estimated_days' => '3-4 days'],
            ['state' => 'Madhya Pradesh', 'fee' => 99.00, 'estimated_days' => '3-4 days'],
            ['state' => 'Chhattisgarh', 'fee' => 99.00, 'estimated_days' => '3-4 days'],
            ['state' => 'Odisha', 'fee' => 99.00, 'estimated_days' => '3-4 days'],
            ['state' => 'Delhi', 'fee' => 99.00, 'estimated_days' => '3-4 days'],
            ['state' => 'Haryana', 'fee' => 99.00, 'estimated_days' => '3-4 days'],
            ['state' => 'Uttar Pradesh', 'fee' => 109.00, 'estimated_days' => '3-4 days'],
            ['state' => 'Rajasthan', 'fee' => 109.00, 'estimated_days' => '3-4 days'],
            ['state' => 'West Bengal', 'fee' => 109.00, 'estimated_days' => '3-4 days'],
            ['state' => 'Punjab', 'fee' => 119.00, 'estimated_days' => '4-5 days'],
            ['state' => 'Chandigarh', 'fee' => 119.00, 'estimated_days' => '4-5 days'],
            ['state' => 'Bihar', 'fee' => 119.00, 'estimated_days' => '4-5 days'],
            ['state' => 'Jharkhand', 'fee' => 119.00, 'estimated_days' => '4-5 days'],
            ['state' => 'Uttarakhand', 'fee' => 129.00, 'estimated_days' => '4-5 days'],
            ['state' => 'Himachal Pradesh', 'fee' => 129.00, 'estimated_days' => '4-5 days'],
            ['state' => 'Assam', 'fee' => 149.00, 'estimated_days' => '5-6 days'],
            ['state' => 'Jammu and Kashmir', 'fee' => 149.00, 'estimated_days' => '5-6 days'],
            ['state' => 'Ladakh', 'fee' => 169.00, 'estimated_days' => '5-6 days'],
            ['state' => 'Sikkim', 'fee' => 149.00, 'estimated_days' => '5-6 days'],
            ['state' => 'Meghalaya', 'fee' => 149.00, 'estimated_days' => '5-6 days'],
            ['state' => 'Manipur', 'fee' => 169.00, 'estimated_days' => '5-7 days'],
            ['state' => 'Nagaland', 'fee' => 169.00, 'estimated_days' => '5-7 days'],
            ['state' => 'Mizoram', 'fee' => 169.00, 'estimated_days' => '5-7 days'],
            ['state' => 'Tripura', 'fee' => 169.00, 'estimated_days' => '5-7 days'],
            ['state' => 'Arunachal Pradesh', 'fee' => 169.00, 'estimated_days' => '5-7 days'],
            ['state' => 'Andaman and Nicobar Islands', 'fee' => 199.00, 'estimated_days' => '6-8 days'],
            ['state' => 'Lakshadweep', 'fee' => 199.00, 'estimated_days' => '6-8 days'],
        ];
    }

    /**
     * Resolve delivery charge and transit estimates based on customer destination state.
     */
    public static function getShippingFeeForState(?string $state): array
    {
        $defaultFee = (float) static::get('default_shipping_fee', 9.99);
        $enableStateShipping = (bool) static::get('enable_state_shipping', true);
        $rates = static::get('state_shipping_rates', []);

        if (! $enableStateShipping || empty(trim((string) $state))) {
            return [
                'fee' => $defaultFee,
                'matched' => false,
                'state' => $state,
                'estimated_days' => null,
            ];
        }

        $cleanState = trim(mb_strtolower((string) $state));

        if (is_array($rates) && ! empty($rates)) {
            foreach ($rates as $key => $rate) {
                $stateName = is_array($rate) ? ($rate['state'] ?? '') : $key;
                $fee = is_array($rate) ? (float) ($rate['fee'] ?? $defaultFee) : (float) $rate;
                $days = is_array($rate) ? ($rate['estimated_days'] ?? null) : null;

                if (trim(mb_strtolower((string) $stateName)) === $cleanState) {
                    return [
                        'fee' => $fee,
                        'matched' => true,
                        'state' => $stateName,
                        'estimated_days' => $days,
                    ];
                }
            }
        }

        return [
            'fee' => $defaultFee,
            'matched' => false,
            'state' => $state,
            'estimated_days' => null,
        ];
    }

    /**
     * Get all cached settings in structured format.
     */
    public static function allCached(): array
    {
        return Cache::rememberForever('site_settings_all', function () {
            $settings = static::all();
            $result = [];

            foreach ($settings as $setting) {
                $result[$setting->key] = [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'raw_value' => $setting->value,
                    'value' => static::formatValue($setting->value, $setting->type),
                    'group' => $setting->group,
                    'type' => $setting->type,
                    'is_public' => $setting->is_public,
                ];
            }

            return $result;
        });
    }

    /**
     * Get all settings grouped by category.
     */
    public static function getAllGrouped(): array
    {
        $cached = static::allCached();
        $grouped = [];

        foreach ($cached as $item) {
            $grouped[$item['group']][$item['key']] = $item['value'];
        }

        return $grouped;
    }

    /**
     * Get public settings for the storefront API.
     */
    public static function getPublicSettings(): array
    {
        $cached = static::allCached();
        $public = [];

        foreach ($cached as $item) {
            if ($item['is_public']) {
                $public[$item['group']][$item['key']] = $item['value'];
            }
        }

        return $public;
    }

    /**
     * Format typed value based on definition.
     */
    public static function formatValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($value) ? (str_contains($value, '.') ? (float) $value : (int) $value) : $value,
            'json' => json_decode($value, true) ?? [],
            'image' => $value ? (str_starts_with($value, 'http') ? $value : asset($value)) : null,
            default => $value,
        };
    }
}
