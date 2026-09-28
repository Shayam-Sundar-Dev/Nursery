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
