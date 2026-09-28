<?php

namespace Tests\Feature;

use App\Models\HomeSlider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSliderManagementTest extends TestCase
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

    public function test_super_admin_and_botanist_can_view_sliders_index(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/sliders');
        $response->assertStatus(200);
        $response->assertSee('Live Botanical Rare Plant Drop');
        $response->assertSee('Low-Light Living Sanctuaries');
        $response->assertSee('Artisan Ceramics & Organic Chunky Blends');

        $responseBotanist = $this->actingAs($this->botanist)->get('/admin/sliders');
        $responseBotanist->assertStatus(200);
        $responseBotanist->assertSee('Live Botanical Rare Plant Drop');
    }

    public function test_unauthorized_roles_cannot_access_sliders(): void
    {
        // Web redirect for unauthorized admin role
        $response = $this->actingAs($this->dispatch)->get('/admin/sliders');
        $response->assertRedirect('/admin');
        $response->assertSessionHas('error');

        // Customer (non-admin) gets 403 Forbidden
        $responseCustomer = $this->actingAs($this->customer)->get('/admin/sliders');
        $responseCustomer->assertStatus(403);

        // Guest redirected to login
        auth()->logout();
        $this->get('/admin/sliders')->assertRedirect('/admin/login');

        // Admin API returns 403 JSON for unauthorized role
        $apiRes = $this->actingAs($this->dispatch, 'sanctum')->getJson('/api/v1/admin/sliders');
        $apiRes->assertStatus(403);
    }

    public function test_admin_can_create_slider_with_local_image_uploads(): void
    {
        $desktopImage = UploadedFile::fake()->image('tropical-hero.jpg', 1600, 800);
        $mobileImage = UploadedFile::fake()->image('tropical-mobile.jpg', 800, 800);

        $response = $this->actingAs($this->superAdmin)->post('/admin/sliders', [
            'title' => 'Spring Equinox Orchid Showcase',
            'subtitle' => 'Exotic flowering epiphytes acclimated for interior garden microclimates.',
            'badge_text' => 'SPECIAL RELEASE',
            'slide_image' => $desktopImage,
            'mobile_slide_image' => $mobileImage,
            'button_text' => 'Discover Orchids',
            'button_link' => '/catalog/orchids',
            'text_align' => 'left',
            'theme' => 'dark',
            'sort_order' => 4,
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/sliders');

        $this->assertDatabaseHas('home_sliders', [
            'title' => 'Spring Equinox Orchid Showcase',
            'button_text' => 'Discover Orchids',
            'sort_order' => 4,
            'is_active' => true,
        ]);

        $slider = HomeSlider::where('title', 'Spring Equinox Orchid Showcase')->first();
        $this->assertNotNull($slider);
        $this->assertStringContainsString('sliders/', $slider->getRawOriginal('image_url'));
        $this->assertStringContainsString('sliders/', $slider->getRawOriginal('mobile_image_url'));

        // Assert file exists in fake public storage disk
        $rawDesktopPath = str_replace('/storage/', '', $slider->getRawOriginal('image_url'));
        $rawMobilePath = str_replace('/storage/', '', $slider->getRawOriginal('mobile_image_url'));

        Storage::disk('public')->assertExists($rawDesktopPath);
        Storage::disk('public')->assertExists($rawMobilePath);
    }

    public function test_admin_can_update_slider_and_replace_image(): void
    {
        $slider = HomeSlider::first();
        $newDesktopImage = UploadedFile::fake()->image('updated-banner.webp', 1920, 1080);

        $response = $this->actingAs($this->botanist)->put("/admin/sliders/{$slider->id}", [
            'title' => 'Updated Rare Plant Collection',
            'subtitle' => 'Refreshed botanical specimens arriving weekly.',
            'slide_image' => $newDesktopImage,
            'button_text' => 'Shop Rare',
            'button_link' => '/catalog/rare-plants',
            'text_align' => 'center',
            'theme' => 'light',
            'sort_order' => 1,
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/sliders');

        $slider->refresh();
        $this->assertEquals('Updated Rare Plant Collection', $slider->title);
        $this->assertEquals('center', $slider->text_align);
        $this->assertEquals('light', $slider->theme);

        $rawPath = str_replace('/storage/', '', $slider->getRawOriginal('image_url'));
        Storage::disk('public')->assertExists($rawPath);
    }

    public function test_admin_can_toggle_slider_active_state(): void
    {
        $slider = HomeSlider::first();
        $this->assertTrue($slider->is_active);

        $response = $this->actingAs($this->superAdmin)->patch("/admin/sliders/{$slider->id}/toggle-active");
        $response->assertRedirect();

        $slider->refresh();
        $this->assertFalse($slider->is_active);
    }

    public function test_admin_can_reorder_sliders(): void
    {
        $sliders = HomeSlider::orderBy('id')->get();
        $this->assertGreaterThanOrEqual(3, $sliders->count());

        $payload = [];
        foreach ($sliders as $index => $slider) {
            $payload[] = [
                'id' => $slider->id,
                'sort_order' => 10 + $index,
            ];
        }

        $response = $this->actingAs($this->superAdmin)->post('/admin/sliders/reorder', [
            'sliders' => $payload,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify updated sort_order values
        foreach ($payload as $item) {
            $this->assertEquals($item['sort_order'], HomeSlider::find($item['id'])->sort_order);
        }
    }

    public function test_admin_can_delete_slider(): void
    {
        $slider = HomeSlider::create([
            'title' => 'Temporary Promo Banner',
            'image_url' => 'https://images.unsplash.com/photo-1596547609652-9cf5d8d76921?auto=format&fit=crop&w=800&q=80',
            'sort_order' => 99,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)->delete("/admin/sliders/{$slider->id}");
        $response->assertRedirect('/admin/sliders');

        $this->assertDatabaseMissing('home_sliders', [
            'id' => $slider->id,
        ]);
    }

    public function test_public_home_slider_api_returns_active_ordered_slides(): void
    {
        // Mark one slider inactive
        $inactiveSlider = HomeSlider::first();
        $inactiveSlider->update(['is_active' => false]);

        $response = $this->getJson('/api/v1/home/sliders');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'subtitle',
                    'badge_text',
                    'image_url',
                    'mobile_image_url',
                    'button_text',
                    'button_link',
                    'text_align',
                    'theme',
                    'sort_order',
                ],
            ],
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);

        // Ensure inactive slider is not present
        $ids = collect($data)->pluck('id')->all();
        $this->assertNotContains($inactiveSlider->id, $ids);

        // Test alias endpoint /api/v1/sliders
        $aliasResponse = $this->getJson('/api/v1/sliders');
        $aliasResponse->assertStatus(200);
        $this->assertEquals(count($data), count($aliasResponse->json('data')));
    }
}
