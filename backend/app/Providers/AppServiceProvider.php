<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('admin.*', function ($view) {
            try {
                $settings = SiteSetting::allCached();
                $currencySymbol = $settings['currency_symbol']['value'] ?? '₹';
                $currencyCode = $settings['currency_code']['value'] ?? 'INR';
                $siteName = $settings['site_name']['value'] ?? 'Verdant Botanical Nursery & Garden';
                $siteTagline = $settings['site_tagline']['value'] ?? 'Live-Plant Specialized Fulfillment & Rare Botanical Specimens';
                $siteLogoUrl = $settings['site_logo_url']['value'] ?? ($settings['site_logo']['value'] ?? null);
                $siteFaviconUrl = $settings['site_favicon_url']['value'] ?? ($settings['site_favicon']['value'] ?? null);
                $maintenanceMode = (bool) ($settings['maintenance_mode']['value'] ?? false);
                $maintenanceMessage = $settings['maintenance_message']['value'] ?? 'Our greenhouses are currently undergoing seasonal botanical catalog updates.';
                $announcementActive = (bool) ($settings['announcement_active']['value'] ?? false);
                $announcementText = $settings['announcement_text']['value'] ?? '';
                $announcementLink = $settings['announcement_link']['value'] ?? '';
                $announcementBgColor = $settings['announcement_bg_color']['value'] ?? '#1b4332';
                $announcementTextColor = $settings['announcement_text_color']['value'] ?? '#d8f3dc';
                $copyrightText = $settings['copyright_text']['value'] ?? null;
                $footerText = $settings['footer_text']['value'] ?? null;
                $contactEmail = $settings['contact_email']['value'] ?? 'care@verdantnursery.test';
                $contactPhone = $settings['contact_phone']['value'] ?? '+1 (555) 321-GROW';
                $freeShippingThreshold = (float) ($settings['free_shipping_threshold']['value'] ?? 75.00);
                $defaultShippingFee = (float) ($settings['default_shipping_fee']['value'] ?? 9.99);
                $thermalPackagingFee = (float) ($settings['thermal_packaging_fee']['value'] ?? 4.50);
            } catch (\Throwable $e) {
                $currencySymbol = '₹';
                $currencyCode = 'INR';
                $siteName = 'Verdant Botanical Nursery & Garden';
                $siteTagline = 'Live-Plant Specialized Fulfillment & Rare Botanical Specimens';
                $siteLogoUrl = null;
                $siteFaviconUrl = null;
                $maintenanceMode = false;
                $maintenanceMessage = '';
                $announcementActive = false;
                $announcementText = '';
                $announcementLink = '';
                $announcementBgColor = '#1b4332';
                $announcementTextColor = '#d8f3dc';
                $copyrightText = null;
                $footerText = null;
                $contactEmail = 'care@verdantnursery.test';
                $contactPhone = '+1 (555) 321-GROW';
                $freeShippingThreshold = 75.00;
                $defaultShippingFee = 9.99;
                $thermalPackagingFee = 4.50;
                $settings = [];
            }

            $view->with([
                'siteSettings' => $settings,
                'currencySymbol' => $currencySymbol,
                'currencyCode' => $currencyCode,
                'siteName' => $siteName,
                'siteTagline' => $siteTagline,
                'siteLogoUrl' => $siteLogoUrl,
                'siteFaviconUrl' => $siteFaviconUrl,
                'maintenanceMode' => $maintenanceMode,
                'maintenanceMessage' => $maintenanceMessage,
                'announcementActive' => $announcementActive,
                'announcementText' => $announcementText,
                'announcementLink' => $announcementLink,
                'announcementBgColor' => $announcementBgColor,
                'announcementTextColor' => $announcementTextColor,
                'copyrightText' => $copyrightText,
                'footerText' => $footerText,
                'contactEmail' => $contactEmail,
                'contactPhone' => $contactPhone,
                'freeShippingThreshold' => $freeShippingThreshold,
                'defaultShippingFee' => $defaultShippingFee,
                'thermalPackagingFee' => $thermalPackagingFee,
            ]);
        });
    }
}
