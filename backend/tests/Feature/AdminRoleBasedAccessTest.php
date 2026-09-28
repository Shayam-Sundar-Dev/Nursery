<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleBasedAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $botanist;

    protected User $dispatch;

    protected User $support;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->superAdmin = User::where('email', 'admin@nursery.test')->first();
        $this->botanist = User::where('email', 'botanist@nursery.test')->first();
        $this->dispatch = User::where('email', 'dispatch@nursery.test')->first();
        $this->support = User::where('email', 'support@nursery.test')->first();
    }

    public function test_super_admin_can_manage_admin_users(): void
    {
        // View admin users list
        $response = $this->actingAs($this->superAdmin)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('Admin Team & Role Access');
        $response->assertSee('Dr. Julian Thorne (Botanist)');

        // Create new admin user
        $createResponse = $this->actingAs($this->superAdmin)->post('/admin/users', [
            'name' => 'Clara Woods',
            'email' => 'clara.woods@nursery.test',
            'password' => 'secret1234',
            'role' => 'botanist',
            'is_active' => '1',
        ]);

        $createResponse->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'email' => 'clara.woods@nursery.test',
            'role' => 'botanist',
            'is_admin' => true,
        ]);
    }

    public function test_super_admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->superAdmin)->delete("/admin/users/{$this->superAdmin->id}");

        $response->assertStatus(302);
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id]);
    }

    public function test_botanist_can_manage_catalog_but_cannot_access_admin_users(): void
    {
        // Botanist can access catalog create page
        $catalogRes = $this->actingAs($this->botanist)->get('/admin/products/create');
        $catalogRes->assertStatus(200);

        // Botanist cannot access admin team management
        $userMgmtRes = $this->actingAs($this->botanist)->get('/admin/users');
        $userMgmtRes->assertRedirect('/admin');
        $userMgmtRes->assertSessionHas('error');
    }

    public function test_botanist_cannot_update_order_transit_status(): void
    {
        $order = Order::first();

        $response = $this->actingAs($this->botanist)
            ->patch("/admin/orders/{$order->id}/status", [
                'status' => 'transit',
            ]);

        $response->assertRedirect('/admin');
        $response->assertSessionHas('error');
    }

    public function test_fulfillment_officer_can_update_orders_and_stock_but_not_catalog(): void
    {
        $order = Order::first();

        // Fulfillment officer updates order status
        $orderRes = $this->actingAs($this->dispatch)
            ->patch("/admin/orders/{$order->id}/status", [
                'status' => 'processing',
            ]);

        $orderRes->assertStatus(302);
        $this->assertEquals('processing', $order->fresh()->status);

        // Fulfillment officer updates inventory stock
        $variant = ProductVariant::first();
        $stockRes = $this->actingAs($this->dispatch)
            ->patch("/admin/inventory/{$variant->id}", [
                'stock_quantity' => 45,
            ]);

        $stockRes->assertStatus(302);
        $this->assertEquals(45, $variant->fresh()->stock_quantity);

        // Fulfillment officer is blocked from creating products
        $prodRes = $this->actingAs($this->dispatch)->get('/admin/products/create');
        $prodRes->assertRedirect('/admin');
        $prodRes->assertSessionHas('error');
    }

    public function test_support_officer_can_view_orders_and_customers_but_cannot_modify(): void
    {
        $order = Order::first();

        // Support can view order
        $viewRes = $this->actingAs($this->support)->get("/admin/orders/{$order->id}");
        $viewRes->assertStatus(200);

        // Support can view customers
        $custRes = $this->actingAs($this->support)->get('/admin/customers');
        $custRes->assertStatus(200);

        // Support cannot modify orders
        $modRes = $this->actingAs($this->support)
            ->patch("/admin/orders/{$order->id}/status", [
                'status' => 'delivered',
            ]);
        $modRes->assertRedirect('/admin');
        $modRes->assertSessionHas('error');
    }

    public function test_inactive_suspended_admin_cannot_access_portal(): void
    {
        $this->botanist->is_active = false;
        $this->botanist->save();

        $response = $this->actingAs($this->botanist)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_api_role_based_access_control(): void
    {
        // Super Admin can list admin users via API
        $superRes = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/v1/admin/users');
        $superRes->assertStatus(200)->assertJsonPath('success', true);

        // Botanist gets 403 on admin users API
        $botanistRes = $this->actingAs($this->botanist, 'sanctum')
            ->getJson('/api/v1/admin/users');
        $botanistRes->assertStatus(403);

        // Botanist can create product via API
        $category = Category::first();
        $prodRes = $this->actingAs($this->botanist, 'sanctum')
            ->postJson('/api/v1/admin/products', [
                'name' => 'Calathea Orbifolia',
                'category_id' => $category->id,
                'type' => 'plant',
                'base_price' => 32.00,
                'primary_image_url' => 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b',
                'description' => 'Stunning rounded silver-striped foliage.',
                'variants' => [
                    [
                        'sku' => 'CAL-ORB-6',
                        'title' => '6" Pot',
                        'price' => 32.00,
                        'stock_quantity' => 15,
                    ],
                ],
            ]);
        $prodRes->assertStatus(201);

        // Dispatch officer gets 403 trying to delete product
        $product = Product::first();
        $delRes = $this->actingAs($this->dispatch, 'sanctum')
            ->deleteJson("/api/v1/admin/products/{$product->id}");
        $delRes->assertStatus(403);
    }
}
