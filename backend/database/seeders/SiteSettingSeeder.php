<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            // General & Branding
            [
                'key' => 'site_name',
                'value' => 'Verdant Botanical Nursery & Garden',
                'group' => 'general',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'site_tagline',
                'value' => 'Live-Plant Specialized Fulfillment & Rare Botanical Specimens',
                'group' => 'general',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'site_logo',
                'value' => null,
                'group' => 'general',
                'type' => 'image',
                'is_public' => true,
            ],
            [
                'key' => 'site_favicon',
                'value' => null,
                'group' => 'general',
                'type' => 'image',
                'is_public' => true,
            ],
            [
                'key' => 'footer_text',
                'value' => 'Cultivating biodiversity, delivering healthy living houseplants directly from our climate-controlled greenhouses.',
                'group' => 'general',
                'type' => 'text',
                'is_public' => true,
            ],
            [
                'key' => 'copyright_text',
                'value' => 'Verdant Botanical Nursery & Garden © 2026. All rights reserved.',
                'group' => 'general',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'currency_symbol',
                'value' => '₹',
                'group' => 'general',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'currency_code',
                'value' => 'INR',
                'group' => 'general',
                'type' => 'string',
                'is_public' => true,
            ],

            // Top Announcement Bar
            [
                'key' => 'announcement_active',
                'value' => '1',
                'group' => 'announcement',
                'type' => 'boolean',
                'is_public' => true,
            ],
            [
                'key' => 'announcement_text',
                'value' => '🌿 Spring Botanical Drop Live! Enjoy 15% off orders over ₹40 with code SPRINGBLOOM',
                'group' => 'announcement',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'announcement_link',
                'value' => '/catalog/indoor-plants',
                'group' => 'announcement',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'announcement_bg_color',
                'value' => '#1b4332',
                'group' => 'announcement',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'announcement_text_color',
                'value' => '#d8f3dc',
                'group' => 'announcement',
                'type' => 'string',
                'is_public' => true,
            ],

            // Shipping & Botanical Transit Policies
            [
                'key' => 'free_shipping_threshold',
                'value' => '75.00',
                'group' => 'shipping',
                'type' => 'number',
                'is_public' => true,
            ],
            [
                'key' => 'default_shipping_fee',
                'value' => '9.99',
                'group' => 'shipping',
                'type' => 'number',
                'is_public' => true,
            ],
            [
                'key' => 'thermal_packaging_fee',
                'value' => '4.50',
                'group' => 'shipping',
                'type' => 'number',
                'is_public' => true,
            ],
            [
                'key' => 'weather_alert_active',
                'value' => '1',
                'group' => 'shipping',
                'type' => 'boolean',
                'is_public' => true,
            ],
            [
                'key' => 'weather_alert_message',
                'value' => '❄️ Live plants ship in custom thermal insulation with 72-hour heat packs to freezing zip codes.',
                'group' => 'shipping',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'guarantee_days',
                'value' => '30',
                'group' => 'shipping',
                'type' => 'number',
                'is_public' => true,
            ],
            [
                'key' => 'guarantee_text',
                'value' => '30-Day Healthy Plant Arrival Guarantee',
                'group' => 'shipping',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'enable_state_shipping',
                'value' => '1',
                'group' => 'shipping',
                'type' => 'boolean',
                'is_public' => true,
            ],
            [
                'key' => 'state_shipping_rates',
                'value' => json_encode(SiteSetting::defaultIndianStateRates()),
                'group' => 'shipping',
                'type' => 'json',
                'is_public' => true,
            ],

            // Contact & Nursery Physical Location
            [
                'key' => 'contact_email',
                'value' => 'care@verdantnursery.test',
                'group' => 'contact',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'contact_phone',
                'value' => '+1 (555) 321-GROW',
                'group' => 'contact',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'contact_whatsapp',
                'value' => '+15553214769',
                'group' => 'contact',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'nursery_address',
                'value' => '742 Evergreen Botanical Way, Greenhouse 4, Portland, OR 97201',
                'group' => 'contact',
                'type' => 'text',
                'is_public' => true,
            ],
            [
                'key' => 'operating_hours',
                'value' => 'Mon - Sat: 8:00 AM - 6:00 PM PST | Sun: 10:00 AM - 4:00 PM PST',
                'group' => 'contact',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'maps_url',
                'value' => 'https://maps.google.com/?q=Portland+Oregon+Botanical+Gardens',
                'group' => 'contact',
                'type' => 'string',
                'is_public' => true,
            ],

            // Social Media & Community Links
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/verdantnursery',
                'group' => 'social',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/verdantnursery',
                'group' => 'social',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'social_pinterest',
                'value' => 'https://pinterest.com/verdantnursery',
                'group' => 'social',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'social_youtube',
                'value' => 'https://youtube.com/@verdantnursery',
                'group' => 'social',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'social_tiktok',
                'value' => 'https://tiktok.com/@verdantnursery',
                'group' => 'social',
                'type' => 'string',
                'is_public' => true,
            ],

            // SEO & Metadata
            [
                'key' => 'meta_title',
                'value' => 'Verdant Nursery | Buy Rare Houseplants & Indoor Flora Online',
                'group' => 'seo',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'meta_description',
                'value' => 'Discover rare split-leaf Monsteras, low-light Dracaenas, handmade ceramic pots, and organic aroid soil delivered safely to your door.',
                'group' => 'seo',
                'type' => 'text',
                'is_public' => true,
            ],
            [
                'key' => 'meta_keywords',
                'value' => 'indoor plants, monstera deliciosa, rare plants, botanical nursery, potted houseplants, buy plants online',
                'group' => 'seo',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'meta_og_image',
                'value' => null,
                'group' => 'seo',
                'type' => 'image',
                'is_public' => true,
            ],

            // Store Status
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'group' => 'status',
                'type' => 'boolean',
                'is_public' => true,
            ],
            [
                'key' => 'maintenance_message',
                'value' => 'We are currently restocking our greenhouses with fresh botanical specimens. Please check back shortly!',
                'group' => 'status',
                'type' => 'text',
                'is_public' => true,
            ],
        ];

        foreach ($defaults as $setting) {
            SiteSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
