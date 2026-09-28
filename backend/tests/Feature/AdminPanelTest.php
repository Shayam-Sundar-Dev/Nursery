<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@nursery.test')->first();
    }

    public function test_admin_dashboard_renders_metrics_and_charts(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Total Botanical Revenue');
        $response->assertSee('Live Orders');
        $response->assertSee('Weather Transit Holds');
        $response->assertSee('Restock Priority Feed');
    }

    public function test_admin_can_view_products_index_with_filters(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/products');

        $response->assertStatus(200);
        $response->assertSee('Monstera Deliciosa');
        $response->assertSee('Botanical Catalog & Products');
    }

    public function test_admin_can_create_new_botanical_product_with_specs_and_variants(): void
    {
        $category = Category::first();

        $payload = [
            'name' => 'Philodendron Pink Princess',
            'botanical_name' => 'Philodendron erubescens',
            'category_id' => $category->id,
            'type' => 'plant',
            'base_price' => 45.00,
            'primary_image_url' => 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?auto=format&fit=crop&w=800&q=80',
            'short_description' => 'Sought-after variegation with deep emerald leaves and bubblegum pink splashes.',
            'description' => 'A stunning climbing philodendron cultivar prized by rare plant collectors around the globe.',
            'is_published' => '1',
            'is_featured' => '1',
            'requires_special_shipping' => '1',
            'light_requirement' => 'Bright Indirect',
            'watering_frequency' => 'Weekly',
            'difficulty_level' => 'Moderate',
            'pet_friendly' => '0',
            'air_purifying' => '1',
            'pot_diameter_inches' => '4.5',
            'variants' => [
                [
                    'sku' => 'PPP-4IN-01',
                    'title' => '4" Nursery Pot / Highly Variegated',
                    'price' => 45.00,
                    'stock_quantity' => 12,
                    'low_stock_threshold' => 3,
                    'weight_grams' => 800,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post('/admin/products', $payload);

        $response->assertRedirect('/admin/products');
        $this->assertDatabaseHas('products', [
            'name' => 'Philodendron Pink Princess',
            'slug' => 'philodendron-pink-princess',
        ]);
        $this->assertDatabaseHas('plant_attributes', [
            'light_requirement' => 'Bright Indirect',
            'difficulty_level' => 'Moderate',
        ]);
        $this->assertDatabaseHas('product_variants', [
            'sku' => 'PPP-4IN-01',
            'stock_quantity' => 12,
        ]);
    }

    public function test_admin_can_toggle_published_and_featured_flags(): void
    {
        $product = Product::first();
        $initialPublished = $product->is_published;

        $response = $this->actingAs($this->admin)
            ->patch("/admin/products/{$product->id}/toggle-publish");

        $response->assertStatus(302);
        $this->assertEquals(! $initialPublished, $product->fresh()->is_published);

        $initialFeatured = $product->is_featured;
        $response2 = $this->actingAs($this->admin)
            ->patch("/admin/products/{$product->id}/toggle-featured");

        $response2->assertStatus(302);
        $this->assertEquals(! $initialFeatured, $product->fresh()->is_featured);
    }

    public function test_admin_can_create_and_update_category(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Carnivorous Plants',
            'description' => 'Bog dwelling insect-trapping specimens including Venus Flytraps and Pitchers.',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/categories');
        $category = Category::where('slug', 'carnivorous-plants')->first();
        $this->assertNotNull($category);

        $updateResponse = $this->actingAs($this->admin)->put("/admin/categories/{$category->id}", [
            'name' => 'Carnivorous & Bog Plants',
            'description' => 'Updated botanical description.',
            'is_active' => '1',
        ]);

        $updateResponse->assertRedirect('/admin/categories');
        $this->assertEquals('Carnivorous & Bog Plants', $category->fresh()->name);
    }

    public function test_admin_can_update_order_status_and_tracking_code(): void
    {
        $order = Order::where('status', 'pending')->first();
        $this->assertNotNull($order);

        $response = $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->id}/status", [
                'status' => 'transit',
                'carrier_name' => 'Botanical Climate Express',
                'tracking_code' => 'EXP-TRK-771920',
                'dispatch_weather_alert_override' => '1',
            ]);

        $response->assertStatus(302);
        $freshOrder = $order->fresh();
        $this->assertEquals('transit', $freshOrder->status);
        $this->assertEquals('EXP-TRK-771920', $freshOrder->tracking_code);
        $this->assertEquals('Botanical Climate Express', $freshOrder->carrier_name);
        $this->assertTrue($freshOrder->dispatch_weather_alert_override);
        $this->assertNotNull($freshOrder->shipped_at);
    }

    public function test_admin_can_update_inventory_stock_quantity(): void
    {
        $variant = ProductVariant::first();

        $response = $this->actingAs($this->admin)
            ->patch("/admin/inventory/{$variant->id}", [
                'stock_quantity' => 75,
                'low_stock_threshold' => 10,
            ]);

        $response->assertStatus(302);
        $this->assertEquals(75, $variant->fresh()->stock_quantity);
    }

    public function test_admin_can_view_customer_and_digital_plant_roster(): void
    {
        $gardener = User::where('email', 'gardener@nursery.test')->first();

        $response = $this->actingAs($this->admin)->get("/admin/customers/{$gardener->id}");

        $response->assertStatus(200);
        $response->assertSee('Flora Vance');
        $response->assertSee('Monty the Monstera');
    }

    public function test_admin_api_endpoints_work_with_sanctum_token(): void
    {
        $token = $this->admin->createToken('test-admin-token', ['admin'])->plainTextToken;

        // Dashboard API
        $dashRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/dashboard');

        $dashRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'financials',
                    'orders',
                    'inventory',
                    'customers',
                    'monthly_revenue',
                ],
            ]);

        // Products API
        $prodRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/products');

        $prodRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // Orders API
        $orderRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/orders');

        $orderRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // Inventory Stock API update
        $variant = ProductVariant::first();
        $invRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson("/api/v1/admin/inventory/{$variant->id}", [
                'stock_quantity' => 50,
            ]);

        $invRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.stock_quantity', 50);
    }
}
