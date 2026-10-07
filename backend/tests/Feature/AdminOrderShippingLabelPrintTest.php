<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderShippingLabelPrintTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Order $processingOrder;
    protected Order $transitOrder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@nursery.test')->first();

        // Ensure site settings for Nursery address exist
        SiteSetting::set('site_name', 'Verdant Botanical Nursery & Garden', 'general', 'string', true);
        SiteSetting::set('nursery_address', '742 Evergreen Botanical Way, Greenhouse 4, Portland, OR 97201', 'contact', 'text', true);
        SiteSetting::set('contact_phone', '+1 (555) 321-GROW', 'contact', 'string', true);
        SiteSetting::set('contact_email', 'care@verdantnursery.test', 'contact', 'string', true);

        $product = Product::first();

        // Create an order in 'processing' status
        $this->processingOrder = Order::create([
            'order_number' => 'ORD-PROC-1001',
            'customer_name' => 'Aarav Patel',
            'customer_email' => 'aarav.patel@test.in',
            'customer_phone' => '+91 98765 43210',
            'status' => 'processing',
            'subtotal' => 1200.00,
            'tax_amount' => 60.00,
            'shipping_fee' => 100.00,
            'insulation_packaging_fee' => 50.00,
            'total_amount' => 1410.00,
            'postal_code' => '560001',
            'shipping_address' => [
                'recipient' => 'Aarav Patel',
                'street' => 'Flat 402, Green Glen Layout, Bellandur',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'postal_code' => '560001',
                'country' => 'India',
                'phone' => '+91 98765 43210',
            ],
            'carrier_name' => 'BlueDart Climate Express',
            'tracking_code' => 'BD-CLIM-889911',
            'gift_message' => 'Happy gardening in your new home!',
        ]);

        OrderItem::create([
            'order_id' => $this->processingOrder->id,
            'product_id' => $product?->id,
            'product_name' => $product?->name ?? 'Monstera Deliciosa',
            'variant_title' => '8" Nursery Pot',
            'unit_price' => 1200.00,
            'quantity' => 1,
            'subtotal' => 1200.00,
        ]);

        // Create an order in 'transit' status
        $this->transitOrder = Order::create([
            'order_number' => 'ORD-TRAN-2002',
            'customer_name' => 'Diya Sharma',
            'customer_email' => 'diya.sharma@test.in',
            'customer_phone' => '+91 91234 56789',
            'status' => 'transit',
            'subtotal' => 800.00,
            'tax_amount' => 40.00,
            'shipping_fee' => 80.00,
            'insulation_packaging_fee' => 0.00,
            'total_amount' => 920.00,
            'postal_code' => '400001',
            'shipping_address' => [
                'recipient' => 'Diya Sharma',
                'street' => '12 Marine Drive',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'postal_code' => '400001',
                'country' => 'India',
            ],
        ]);
    }

    public function test_print_shipping_label_button_appears_only_on_processing_filter(): void
    {
        // 1. On Processing & Packaging filter -> MUST show banner and print buttons
        $responseProcessing = $this->actingAs($this->admin)->get('/admin/orders?status=processing');
        $responseProcessing->assertStatus(200);
        $responseProcessing->assertSee('Shipping Address Labels (From - To)');
        $responseProcessing->assertSee('Print All Shipping Labels');
        $responseProcessing->assertSee('Print Label');
        $responseProcessing->assertSee($this->processingOrder->order_number);

        // 2. On Transit filter -> MUST NOT show banner or print buttons
        $responseTransit = $this->actingAs($this->admin)->get('/admin/orders?status=transit');
        $responseTransit->assertStatus(200);
        $responseTransit->assertDontSee('Shipping Address Labels (From - To)');
        $responseTransit->assertDontSee('Print All Shipping Labels');
        $responseTransit->assertDontSee('Print Label');

        // 3. On Pending filter -> MUST NOT show banner or print buttons
        $responsePending = $this->actingAs($this->admin)->get('/admin/orders?status=pending');
        $responsePending->assertStatus(200);
        $responsePending->assertDontSee('Shipping Address Labels (From - To)');
        $responsePending->assertDontSee('Print All Shipping Labels');

        // 4. On All Orders -> MUST NOT show banner or print buttons
        $responseAll = $this->actingAs($this->admin)->get('/admin/orders');
        $responseAll->assertStatus(200);
        $responseAll->assertDontSee('Shipping Address Labels (From - To)');
        $responseAll->assertDontSee('Print All Shipping Labels');
    }

    public function test_admin_can_print_shipping_label_for_processing_order(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/orders/{$this->processingOrder->id}/print-shipping-label");

        $response->assertStatus(200);

        // FROM verification (Nursery Details)
        $response->assertSee('FROM / SENDER / RETURN ADDRESS');
        $response->assertSee('Verdant Botanical Nursery');
        $response->assertSee('742 Evergreen Botanical Way');
        $response->assertSee('+1 (555) 321-GROW');
        $response->assertSee('care@verdantnursery.test');

        // TO verification (Customer Shipping Details)
        $response->assertSee('SHIP TO / DELIVER TO (RECIPIENT)');
        $response->assertSee('Aarav Patel');
        $response->assertSee('Flat 402, Green Glen Layout, Bellandur');
        $response->assertSee('Bengaluru');
        $response->assertSee('Karnataka');
        $response->assertSee('560001');
        $response->assertSee('+91 98765 43210');

        // Package and Handling Verification
        $response->assertSee('ORD-PROC-1001');
        $response->assertSee('Processing & Packaging', false);
        $response->assertSee('Thermal Pack Enclosed');
        $response->assertSee('BlueDart Climate Express');
        $response->assertSee('Happy gardening in your new home!');
    }

    public function test_printing_shipping_label_for_non_processing_order_is_blocked(): void
    {
        // Orders not in 'processing' status must be blocked and redirected
        $response = $this->actingAs($this->admin)->get("/admin/orders/{$this->transitOrder->id}/print-shipping-label");

        $response->assertStatus(302);
        $response->assertRedirect('/admin/orders?status=processing');
        $response->assertSessionHas('error');
    }

    public function test_admin_can_bulk_print_shipping_labels_for_processing_orders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/orders/print-shipping-labels');

        $response->assertStatus(200);
        $response->assertSee('ORD-PROC-1001');
        $response->assertSee('Aarav Patel');
        $response->assertSee('FROM / SENDER / RETURN ADDRESS');
        $response->assertSee('SHIP TO / DELIVER TO (RECIPIENT)');
    }

    public function test_bulk_print_filters_by_selected_order_ids(): void
    {
        // Create second processing order
        $order2 = Order::create([
            'order_number' => 'ORD-PROC-1002',
            'customer_name' => 'Meera Nair',
            'customer_email' => 'meera@test.in',
            'customer_phone' => '+91 99887 76655',
            'status' => 'processing',
            'subtotal' => 500.00,
            'total_amount' => 500.00,
            'postal_code' => '682001',
            'shipping_address' => [
                'recipient' => 'Meera Nair',
                'street' => 'MG Road',
                'city' => 'Kochi',
                'state' => 'Kerala',
                'postal_code' => '682001',
                'country' => 'India',
            ],
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/orders/print-shipping-labels?order_ids={$order2->id}");

        $response->assertStatus(200);
        $response->assertSee('ORD-PROC-1002');
        $response->assertSee('Meera Nair');
        $response->assertDontSee('ORD-PROC-1001');
    }

    public function test_order_show_page_displays_print_button_only_when_processing(): void
    {
        // Processing order must show print button
        $respProc = $this->actingAs($this->admin)->get("/admin/orders/{$this->processingOrder->id}");
        $respProc->assertStatus(200);
        $respProc->assertSee('Print Shipping Address (From - To)');

        // Transit order must not show print button
        $respTran = $this->actingAs($this->admin)->get("/admin/orders/{$this->transitOrder->id}");
        $respTran->assertStatus(200);
        $respTran->assertDontSee('Print Shipping Address (From - To)');
    }

    public function test_unauthenticated_user_cannot_access_shipping_label_print(): void
    {
        $response = $this->get("/admin/orders/{$this->processingOrder->id}/print-shipping-label");
        $response->assertRedirect('/admin/login');

        $responseBulk = $this->get('/admin/orders/print-shipping-labels');
        $responseBulk->assertRedirect('/admin/login');
    }
}
