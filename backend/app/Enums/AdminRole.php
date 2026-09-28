<?php

namespace App\Enums;

enum AdminRole: string
{
    case SuperAdmin = 'super_admin';
    case Botanist = 'botanist';
    case Fulfillment = 'fulfillment';
    case Support = 'support';
    case Customer = 'customer';

    /**
     * Get human-readable title.
     */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrator',
            self::Botanist => 'Head Botanist / Catalog Manager',
            self::Fulfillment => 'Logistics & Dispatch Officer',
            self::Support => 'Customer Care & Plant Guide',
            self::Customer => 'Registered Gardener / Customer',
        };
    }

    /**
     * Get a description of access responsibilities.
     */
    public function description(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Full unrestricted access to all nursery operations, financials, catalog, orders, and admin team management.',
            self::Botanist => 'Curates botanical taxonomy, care instructions, light/watering attributes, product images, categories, and inventory stock.',
            self::Fulfillment => 'Manages order processing, live-plant transit tracking, carrier logistics, weather dispatch holds, and variant stock.',
            self::Support => 'Assists customers with order inquiries, view order histories, and reviews customer digital garden companion plants.',
            self::Customer => 'Customer store shopper with personal account and digital garden companion access.',
        };
    }

    /**
     * Color styling for UI badges.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::SuperAdmin => 'bg-purple-100 text-purple-800 border border-purple-200',
            self::Botanist => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
            self::Fulfillment => 'bg-blue-100 text-blue-800 border border-blue-200',
            self::Support => 'bg-amber-100 text-amber-800 border border-amber-200',
            self::Customer => 'bg-stone-100 text-stone-700 border border-stone-200',
        };
    }

    /**
     * Permissions granted to this role.
     */
    public function permissions(): array
    {
        return match ($this) {
            self::SuperAdmin => [
                'manage_admins',
                'manage_catalog',
                'manage_categories',
                'manage_orders',
                'manage_transit',
                'manage_inventory',
                'manage_customers',
                'view_financials',
            ],
            self::Botanist => [
                'manage_catalog',
                'manage_categories',
                'manage_inventory',
            ],
            self::Fulfillment => [
                'manage_orders',
                'manage_transit',
                'manage_inventory',
            ],
            self::Support => [
                'view_orders',
                'manage_customers',
            ],
            self::Customer => [],
        };
    }

    /**
     * List all administrative roles for assignment.
     */
    public static function adminRoles(): array
    {
        return [
            self::SuperAdmin,
            self::Botanist,
            self::Fulfillment,
            self::Support,
        ];
    }
}
