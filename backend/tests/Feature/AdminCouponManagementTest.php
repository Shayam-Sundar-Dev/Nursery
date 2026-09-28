<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCouponManagementTest extends TestCase
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

        $this->superAdmin = User::where('email', 'admin@nursery.test')->first();
        $this->botanist = User::where('email', 'botanist@nursery.test')->first();
        $this->dispatch = User::where('email', 'dispatch@nursery.test')->first();
        $this->customer = User::where('email', 'gardener@nursery.test')->first();
    }

    public function test_super_admin_and_botanist_can_view_coupons_index(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/coupons');
        $response->assertStatus(200);
        $response->assertSee('SPRINGBLOOM');
        $response->assertSee('WELCOME10');
        $response->assertSee('FREESHIP');

        $responseBotanist = $this->actingAs($this->botanist)->get('/admin/coupons');
        $responseBotanist->assertStatus(200);
        $responseBotanist->assertSee('SPRINGBLOOM');
    }

    public function test_unauthorized_roles_cannot_access_coupons(): void
    {
        // Web redirect for unauthorized admin role
        $response = $this->actingAs($this->dispatch)->get('/admin/coupons');
        $response->assertRedirect('/admin');
        $response->assertSessionHas('error');

        // Customer (non-admin) gets 403 Forbidden
        $responseCustomer = $this->actingAs($this->customer)->get('/admin/coupons');
        $responseCustomer->assertStatus(403);

        // Guest redirected to login
        auth()->logout();
        $this->get('/admin/coupons')->assertRedirect('/admin/login');

        // Admin API returns 403 JSON for unauthorized admin role
        $apiRes = $this->actingAs($this->dispatch, 'sanctum')->getJson('/api/v1/admin/coupons');
        $apiRes->assertStatus(403);
    }

    public function test_admin_can_create_a_coupon(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/admin/coupons', [
            'code' => 'SUMMER25',
            'description' => '25% off rare summer tropicals',
            'discount_type' => 'percentage',
            'discount_amount' => 25.00,
            'min_order_amount' => 50.00,
            'max_discount_amount' => 30.00,
            'usage_limit' => 100,
            'per_user_limit' => 1,
            'starts_at' => now()->format('Y-m-d\TH:i'),
            'expires_at' => now()->addDays(30)->format('Y-m-d\TH:i'),
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/coupons');
        $this->assertDatabaseHas('coupons', [
            'code' => 'SUMMER25',
            'discount_type' => 'percentage',
            'discount_amount' => 25.00,
            'is_active' => true,
        ]);
    }

    public function test_coupon_code_must_be_unique(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/admin/coupons', [
            'code' => 'SPRINGBLOOM', // Already seeded
            'description' => 'Duplicate code',
            'discount_type' => 'percentage',
            'discount_amount' => 10.00,
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_admin_can_update_a_coupon(): void
    {
        $coupon = Coupon::where('code', 'WELCOME10')->first();

        $response = $this->actingAs($this->botanist)->put("/admin/coupons/{$coupon->id}", [
            'code' => 'WELCOME15',
            'description' => '₹15 Off First Order',
            'discount_type' => 'fixed',
            'discount_amount' => 15.00,
            'min_order_amount' => 45.00,
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/coupons');
        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'code' => 'WELCOME15',
            'discount_amount' => 15.00,
        ]);
    }

    public function test_admin_can_toggle_coupon_active_state(): void
    {
        $coupon = Coupon::where('code', 'FREESHIP')->first();
        $this->assertTrue($coupon->is_active);

        $response = $this->actingAs($this->superAdmin)->patch("/admin/coupons/{$coupon->id}/toggle-active");
        $response->assertRedirect();

        $coupon->refresh();
        $this->assertFalse($coupon->is_active);
    }

    public function test_customer_can_validate_coupon_via_api(): void
    {
        // 1. Valid coupon meeting min order
        $response = $this->postJson('/api/v1/coupons/validate', [
            'code' => 'SPRINGBLOOM',
            'subtotal' => 60.00,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'coupon' => [
                'code' => 'SPRINGBLOOM',
                'discount_type' => 'percentage',
                'calculated_discount' => 9.00, // 15% of 60.00
            ],
        ]);

        // 2. Below minimum order requirement
        $responseBelow = $this->postJson('/api/v1/coupons/validate', [
            'code' => 'SPRINGBLOOM', // min order 40.00
            'subtotal' => 30.00,
        ]);

        $responseBelow->assertStatus(422);
        $responseBelow->assertJson([
            'success' => false,
        ]);

        // 3. Inactive coupon
        $inactive = Coupon::create([
            'code' => 'INACTIVE99',
            'discount_type' => 'fixed',
            'discount_amount' => 10.00,
            'is_active' => false,
        ]);

        $responseInactive = $this->postJson('/api/v1/coupons/validate', [
            'code' => 'INACTIVE99',
            'subtotal' => 50.00,
        ]);

        $responseInactive->assertStatus(422);
        $responseInactive->assertJson([
            'success' => false,
        ]);
    }

    public function test_checkout_applies_coupon_discount_and_records_usage(): void
    {
        $variant = ProductVariant::where('sku', 'MON-MED-6')->first();
        $coupon = Coupon::where('code', 'WELCOME10')->first();
        $initialUsage = $coupon->usage_count;

        $response = $this->actingAs($this->customer, 'sanctum')->postJson('/api/v1/checkout/orders', [
            'customer_name' => 'Flora Vance',
            'customer_email' => 'gardener@nursery.test',
            'shipping_address' => [
                'recipient' => 'Flora Vance',
                'street' => '742 Evergreen Terrace',
                'city' => 'Portland',
                'state' => 'OR',
                'postal_code' => '97201',
                'country' => 'US',
            ],
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'quantity' => 1,
                ],
            ],
            'coupon_code' => 'WELCOME10',
        ]);

        $response->assertStatus(201);
        $data = $response->json('order');

        $this->assertEquals('WELCOME10', $data['coupon_code']);
        $this->assertEquals(10.00, $data['discount_amount']);

        $coupon->refresh();
        $this->assertEquals($initialUsage + 1, $coupon->usage_count);
    }
}
