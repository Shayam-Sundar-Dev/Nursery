<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@nursery.test')->first();
    }

    public function test_admin_can_view_product_types_index_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/product-types');

        $response->assertStatus(200);
        $response->assertSee('Product Types Management');
        $response->assertSee('Live Houseplant / Botanical');
        $response->assertSee('Pot & Ceramic Planter');
        $response->assertSee('Soil & Plant Nutrition');
    }

    public function test_admin_can_create_new_product_type(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/product-types', [
            'name' => 'Bonsai Trees & Specimens',
            'slug' => 'bonsai',
            'description' => 'Miniature stylized potted trees requiring precision care and wiring.',
            'icon' => 'leaf',
            'badge_color' => 'indigo',
            'requires_botanical_attributes' => '1',
            'is_active' => '1',
            'sort_order' => 10,
        ]);

        $response->assertRedirect('/admin/product-types');
        $this->assertDatabaseHas('product_types', [
            'name' => 'Bonsai Trees & Specimens',
            'slug' => 'bonsai',
            'badge_color' => 'indigo',
            'requires_botanical_attributes' => true,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_product_type_and_cascade_slug_to_products(): void
    {
        // Create product type
        $type = ProductType::create([
            'name' => 'Terrarium Glass',
            'slug' => 'terrarium-glass',
            'badge_color' => 'teal',
            'is_active' => true,
        ]);

        // Create a product with this type
        $category = Category::first();
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Geometric Glass Wardian Case',
            'slug' => 'geometric-glass-wardian-case',
            'type' => 'terrarium-glass',
            'base_price' => 55.00,
            'description' => 'Hand-soldered brass and glass terrarium vessel.',
            'is_published' => true,
        ]);

        // Update the product type with a renamed slug
        $response = $this->actingAs($this->admin)->put("/admin/product-types/{$type->id}", [
            'name' => 'Glass Terrariums & Wardian Cases',
            'slug' => 'terrarium',
            'badge_color' => 'teal',
            'requires_botanical_attributes' => '0',
            'is_active' => '1',
            'sort_order' => 8,
        ]);

        $response->assertRedirect('/admin/product-types');
        $this->assertDatabaseHas('product_types', [
            'id' => $type->id,
            'name' => 'Glass Terrariums & Wardian Cases',
            'slug' => 'terrarium',
        ]);

        // Verify the product's type was updated automatically
        $this->assertEquals('terrarium', $product->fresh()->type);
    }

    public function test_admin_can_toggle_active_status_of_product_type(): void
    {
        $type = ProductType::where('slug', 'seed')->first();
        $initialStatus = $type->is_active;

        $response = $this->actingAs($this->admin)->patch("/admin/product-types/{$type->id}/toggle-active");

        $response->assertRedirect('/admin/product-types');
        $this->assertEquals(! $initialStatus, $type->fresh()->is_active);
    }

    public function test_admin_cannot_delete_product_type_with_assigned_products(): void
    {
        // 'plant' is used by seeded products
        $plantType = ProductType::where('slug', 'plant')->first();
        $this->assertGreaterThan(0, $plantType->products()->count());

        $response = $this->actingAs($this->admin)->delete("/admin/product-types/{$plantType->id}");

        $response->assertRedirect('/admin/product-types');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('product_types', ['id' => $plantType->id]);
    }

    public function test_admin_can_delete_unused_product_type(): void
    {
        $unusedType = ProductType::create([
            'name' => 'Unused Merch Type',
            'slug' => 'unused-merch',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/product-types/{$unusedType->id}");

        $response->assertRedirect('/admin/product-types');
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('product_types', ['id' => $unusedType->id]);
    }

    public function test_public_api_returns_active_product_types_with_counts(): void
    {
        $response = $this->getJson('/api/v1/product-types');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'icon',
                    'badge_color',
                    'requires_botanical_attributes',
                    'products_count',
                ],
            ],
        ]);

        $data = collect($response->json('data'))->keyBy('slug');
        $this->assertTrue($data->has('plant'));
        $this->assertGreaterThan(0, $data['plant']['products_count']);
    }

    public function test_admin_api_product_types_endpoints(): void
    {
        // List product types via API
        $token = $this->admin->createToken('admin-test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/product-types');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Create product type via API
        $createResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/admin/product-types', [
                'name' => 'Rare Bulbs & Tubers',
                'slug' => 'bulbs',
                'description' => 'Cold-stratified bulbs and tropical tubers.',
                'icon' => 'sprout',
                'badge_color' => 'rose',
                'requires_botanical_attributes' => false,
                'is_active' => true,
            ]);

        $createResponse->assertStatus(201);
        $createdId = $createResponse->json('data.id');

        // Update product type via API
        $updateResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/v1/admin/product-types/{$createdId}", [
                'name' => 'Rare Bulbs, Corms & Tubers',
                'slug' => 'bulbs-corms',
                'badge_color' => 'rose',
                'is_active' => true,
            ]);

        $updateResponse->assertStatus(200);
        $this->assertEquals('Rare Bulbs, Corms & Tubers', $updateResponse->json('data.name'));

        // Delete product type via API
        $deleteResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/admin/product-types/{$createdId}");

        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('product_types', ['id' => $createdId]);
    }
}

