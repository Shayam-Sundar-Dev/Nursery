<?php

namespace Tests\Feature;

use App\Models\ProductVariant;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSiteManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $botanist;

    protected User $dispatch;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        Storage::fake('public');

        $this->superAdmin = User::where('email', 'admin@nursery.test')->first();
        $this->botanist = User::where('email', 'botanist@nursery.test')->first();
        $this->dispatch = User::where('email', 'dispatch@nursery.test')->first();
        $this->customer = User::where('email', 'gardener@nursery.test')->first();
    }

    public function test_super_admin_can_view_site_settings_panel(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/settings');
        $response->assertStatus(200);
        $response->assertSee('Site & Storefront Management');
        $response->assertSee('Verdant Botanical Nursery & Garden');
        $response->assertSee('Free Shipping Threshold');
        $response->assertSee('Announcement Bar');
    }

    public function test_non_super_admin_roles_cannot_access_site_settings(): void
    {
        // Botanist restricted
        $resBotanist = $this->actingAs($this->botanist)->get('/admin/settings');
        $resBotanist->assertRedirect('/admin');
        $resBotanist->assertSessionHas('error');

        // Dispatch restricted
        $resDispatch = $this->actingAs($this->dispatch)->get('/admin/settings');
        $resDispatch->assertRedirect('/admin');
        $resDispatch->assertSessionHas('error');

        // Normal Customer
        $resCustomer = $this->actingAs($this->customer)->get('/admin/settings');
        $resCustomer->assertStatus(403);

        // Guest
        auth()->logout();
        $this->get('/admin/settings')->assertRedirect('/admin/login');

        // API endpoints
        $apiBotanist = $this->actingAs($this->botanist, 'sanctum')->getJson('/api/v1/admin/settings');
        $apiBotanist->assertStatus(403);
    }

    public function test_super_admin_can_update_site_settings_with_file_uploads(): void
    {
        $logo = UploadedFile::fake()->image('custom-logo.png', 400, 100);
        $favicon = UploadedFile::fake()->create('custom-favicon.ico', 32, 'image/x-icon');
        $ogImage = UploadedFile::fake()->image('custom-og.jpg', 1200, 630);

        $response = $this->actingAs($this->superAdmin)->post('/admin/settings', [
            'site_name' => 'Verdant Rare Flora & Nursery',
            'site_tagline' => 'Climate-Regulated Live Houseplant Shipping',
            'site_logo_file' => $logo,
            'site_favicon_file' => $favicon,
            'meta_og_image_file' => $ogImage,
            'has_announcement_form' => '1',
            'announcement_active' => '1',
            'announcement_text' => 'Flash Sale: 20% off all Aroids this weekend!',
            'announcement_bg_color' => '#064e3b',
            'announcement_text_color' => '#ecfdf5',
            'has_shipping_form' => '1',
            'free_shipping_threshold' => '65.00',
            'default_shipping_fee' => '8.50',
            'thermal_packaging_fee' => '5.00',
            'weather_alert_active' => '1',
            'weather_alert_message' => 'Arctic blast advisory in Midwest zip codes.',
            'contact_email' => 'hello@verdantrareplants.test',
            'contact_phone' => '+1 (555) 999-GROW',
            'has_status_form' => '1',
            'maintenance_mode' => '0',
        ]);

        $response->assertRedirect('/admin/settings?tab=general');
        $response->assertSessionHas('success');

        // Assert database values
        $this->assertEquals('Verdant Rare Flora & Nursery', SiteSetting::get('site_name'));
        $this->assertEquals('Climate-Regulated Live Houseplant Shipping', SiteSetting::get('site_tagline'));
        $this->assertEquals('Flash Sale: 20% off all Aroids this weekend!', SiteSetting::get('announcement_text'));
        $this->assertEquals(65.00, (float) SiteSetting::get('free_shipping_threshold'));
        $this->assertEquals(8.50, (float) SiteSetting::get('default_shipping_fee'));
        $this->assertEquals(5.00, (float) SiteSetting::get('thermal_packaging_fee'));
        $this->assertEquals('hello@verdantrareplants.test', SiteSetting::get('contact_email'));

        // Assert storage uploads exist
        $rawLogo = SiteSetting::where('key', 'site_logo')->value('value');
        $rawFavicon = SiteSetting::where('key', 'site_favicon')->value('value');
        $rawOg = SiteSetting::where('key', 'meta_og_image')->value('value');

        $this->assertStringContainsString('site/', $rawLogo);
        $this->assertStringContainsString('site/', $rawFavicon);
        $this->assertStringContainsString('site/', $rawOg);

        Storage::disk('public')->assertExists(str_replace('/storage/', '', $rawLogo));
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $rawFavicon));
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $rawOg));
    }

    public function test_public_site_settings_api(): void
    {
        $response = $this->getJson('/api/v1/site-settings');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'general' => ['site_name', 'site_tagline', 'footer_text', 'copyright_text', 'currency_symbol', 'currency_code'],
                'announcement' => ['announcement_active', 'announcement_text', 'announcement_link'],
                'shipping' => ['free_shipping_threshold', 'default_shipping_fee', 'thermal_packaging_fee'],
                'contact' => ['contact_email', 'contact_phone', 'nursery_address'],
                'social' => ['social_instagram', 'social_facebook'],
                'seo' => ['meta_title', 'meta_description'],
                'status' => ['maintenance_mode'],
            ],
        ]);

        $this->assertEquals('₹', $response->json('data.general.currency_symbol'));
        $this->assertEquals('INR', $response->json('data.general.currency_code'));

        // Test alias endpoint
        $alias = $this->getJson('/api/v1/settings');
        $alias->assertStatus(200);
        $this->assertEquals($response->json('data'), $alias->json('data'));
    }

    public function test_admin_api_site_settings_crud(): void
    {
        // 1. Get settings
        $response = $this->actingAs($this->superAdmin, 'sanctum')->getJson('/api/v1/admin/settings');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 2. Update settings headlessly
        $updateRes = $this->actingAs($this->superAdmin, 'sanctum')->postJson('/api/v1/admin/settings', [
            'settings' => [
                'site_name' => 'Botanica Deluxe Greenhouse',
                'free_shipping_threshold' => 90.00,
                'announcement_text' => 'Winter Greenhouse Tour Tickets Now Available',
            ],
        ]);

        $updateRes->assertStatus(200);
        $updateRes->assertJson(['success' => true]);

        $this->assertEquals('Botanica Deluxe Greenhouse', SiteSetting::get('site_name'));
        $this->assertEquals(90.00, (float) SiteSetting::get('free_shipping_threshold'));
    }

    public function test_checkout_dynamically_adapts_to_site_setting_thresholds(): void
    {
        $variant = ProductVariant::where('sku', 'MON-MED-6')->first(); // price: $38.00

        // 1. Set free shipping threshold to $30.00 (below $38 subtotal -> free shipping)
        SiteSetting::set('free_shipping_threshold', 30.00, 'shipping', 'number');
        SiteSetting::set('default_shipping_fee', 12.00, 'shipping', 'number');

        $responseFree = $this->postJson('/api/v1/checkout/summary', [
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => 1],
            ],
        ]);

        $responseFree->assertStatus(200);
        $this->assertEquals(0.00, $responseFree->json('breakdown.shipping'));
        $this->assertTrue($responseFree->json('breakdown.qualifies_for_free_shipping'));

        // 2. Set free shipping threshold to $100.00 (above $38 subtotal -> charged default fee $12.00)
        SiteSetting::set('free_shipping_threshold', 100.00, 'shipping', 'number');

        $responsePaid = $this->postJson('/api/v1/checkout/summary', [
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => 1],
            ],
        ]);

        $responsePaid->assertStatus(200);
        $this->assertEquals(12.00, $responsePaid->json('breakdown.shipping'));
        $this->assertFalse($responsePaid->json('breakdown.qualifies_for_free_shipping'));
    }

    public function test_site_management_settings_reflect_in_admin_panel_views(): void
    {
        // 1. Configure custom site settings
        SiteSetting::set('site_name', 'Grand Botanical Conservatory', 'general', 'string');
        SiteSetting::set('site_tagline', 'Exotic Specimens & Rare Ferns', 'general', 'string');
        SiteSetting::set('maintenance_mode', 1, 'status', 'boolean');
        SiteSetting::set('maintenance_message', 'Spring catalog overhaul in progress.', 'status', 'string');
        SiteSetting::set('announcement_active', 1, 'announcement', 'boolean');
        SiteSetting::set('announcement_text', 'Special 25% plant flash sale live today!', 'announcement', 'string');

        // 2. View Admin Dashboard
        $response = $this->actingAs($this->superAdmin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Grand Botanical Conservatory');
        $response->assertSee('STOREFRONT MAINTENANCE ACTIVE');
        $response->assertSee('Spring catalog overhaul in progress.');
        $response->assertSee('Live Storefront Banner');
        $response->assertSee('Special 25% plant flash sale live today!');

        // 3. View Admin Login Page (as unauthenticated guest)
        auth()->logout();
        $responseLogin = $this->get('/admin/login');
        $responseLogin->assertStatus(200);
        $responseLogin->assertSee('Grand Botanical Conservatory Admin Portal');
        $responseLogin->assertSee('Exotic Specimens & Rare Ferns');
        $responseLogin->assertSee('Storefront is in Maintenance Mode (Admin active)');
    }
}
