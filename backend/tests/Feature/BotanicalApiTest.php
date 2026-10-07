<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserPlant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BotanicalApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_categories_endpoint_returns_accurate_product_counts_and_child_category_rollup(): void
    {
        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $categories = collect($response->json('data'))->keyBy('slug');

        // Indoor Plants (slug: indoor-plants) has 2 direct + 2 in Low Light Champions (parent_id: 1) = 4 total
        $this->assertEquals(4, $categories['indoor-plants']['products_count']);
        // Pots & Planters (slug: pots-and-planters) has 1
        $this->assertEquals(1, $categories['pots-and-planters']['products_count']);
        // Soil & Plant Nutrition (slug: soil-and-nutrition) has 1
        $this->assertEquals(1, $categories['soil-and-nutrition']['products_count']);

        // Filtering products by indoor-plants should return all 4 indoor plants including children
        $productsResponse = $this->getJson('/api/v1/products?category=indoor-plants');
        $productsResponse->assertStatus(200);
        $this->assertCount(4, $productsResponse->json('data'));
    }

    public function test_catalog_endpoint_returns_paginated_products_with_botanical_specs(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'botanical_name',
                        'slug',
                        'type',
                        'base_price',
                        'plant_attributes',
                        'variants',
                    ],
                ],
                'meta',
            ]);
    }

    public function test_botanical_filter_by_light_and_pet_friendly(): void
    {
        $response = $this->getJson('/api/v1/products?light=Bright+Indirect&pet_friendly=true');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $this->assertEquals('Bright Indirect', $item['plant_attributes']['light_requirement']);
            $this->assertTrue($item['plant_attributes']['pet_friendly']);
        }
    }

    public function test_single_product_returns_botanical_specs_and_matching_planters(): void
    {
        $response = $this->getJson('/api/v1/products/monstera-deliciosa');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Monstera Deliciosa')
            ->assertJsonPath('data.botanical_name', 'Monstera deliciosa');

        $matchingPlanters = $response->json('matching_planters');
        $this->assertIsArray($matchingPlanters);
        $this->assertNotEmpty($matchingPlanters);
    }

    public function test_plant_finder_quiz_recommends_suitable_plants(): void
    {
        $payload = [
            'light_level' => 'bright_indirect',
            'care_routine' => 'forgetful',
            'has_pets' => false,
        ];

        $response = $this->postJson('/api/v1/plant-finder', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $recommendations = $response->json('recommendations');
        $this->assertNotEmpty($recommendations);
    }

    public function test_deliverability_check_identifies_live_plant_shipping_and_thermal_fee(): void
    {
        // Cold northern zip code starting with 0 requires thermal insulation
        $coldResponse = $this->postJson('/api/v1/shipping/check-deliverability', [
            'postal_code' => '02138',
        ]);

        $coldResponse->assertStatus(200)
            ->assertJsonPath('is_deliverable', true)
            ->assertJsonPath('requires_thermal_packaging', true)
            ->assertJsonPath('insulation_fee', 4.5);

        // Remote unsupported zip code starting with 999
        $unsupportedResponse = $this->postJson('/api/v1/shipping/check-deliverability', [
            'postal_code' => '99901',
        ]);

        $unsupportedResponse->assertStatus(200)
            ->assertJsonPath('is_deliverable', false);
    }

    public function test_checkout_summary_calculates_subtotal_tax_and_insulation(): void
    {
        $variant = ProductVariant::first();

        $response = $this->postJson('/api/v1/checkout/summary', [
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => 2],
            ],
            'postal_code' => '02138',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'breakdown' => [
                    'subtotal',
                    'tax',
                    'shipping',
                    'insulation_fee',
                    'total',
                ],
            ]);
    }

    public function test_atomic_checkout_order_creation_and_inventory_decrement(): void
    {
        $variant = ProductVariant::where('stock_quantity', '>', 5)->first();
        $initialStock = $variant->stock_quantity;
        $orderQty = 2;

        $payload = [
            'customer_name' => 'Rose Gardner',
            'customer_email' => 'rose@botanical.test',
            'customer_phone' => '+15551234567',
            'shipping_address' => [
                'street' => '124 Fern Valley Lane',
                'city' => 'Portland',
                'state' => 'OR',
                'postal_code' => '97201',
            ],
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => $orderQty],
            ],
            'gift_message' => 'Happy Housewarming with a leafy friend!',
        ];

        $response = $this->postJson('/api/v1/checkout/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $orderNumber = $response->json('order.order_number');
        $this->assertNotEmpty($orderNumber);

        // Verify stock decremented
        $variant->refresh();
        $this->assertEquals($initialStock - $orderQty, $variant->stock_quantity);

        // Verify order saved in database
        $this->assertDatabaseHas('orders', [
            'order_number' => $orderNumber,
            'customer_email' => 'rose@botanical.test',
        ]);

        // Verify live order tracking
        $trackingResponse = $this->getJson("/api/v1/orders/{$orderNumber}/track");
        $trackingResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('order_number', $orderNumber)
            ->assertJsonStructure(['milestones', 'unboxing_advice']);
    }

    public function test_user_plant_companion_watering_log(): void
    {
        $userPlant = UserPlant::first();
        $this->assertNotNull($userPlant);

        $response = $this->patchJson("/api/v1/my-plants/{$userPlant->id}/water");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $userPlant->refresh();
        $this->assertNotNull($userPlant->last_watered_at);
        $this->assertFalse($userPlant->isWateringOverdue());
    }

    public function test_catalog_handles_frontend_sort_and_category_slug_parameters(): void
    {
        // Popular sort (default in frontend)
        $res1 = $this->getJson('/api/v1/products?sort=popular');
        $res1->assertStatus(200)->assertJsonPath('success', true);
        $this->assertNotEmpty($res1->json('data'));

        // Category by slug with popular sort
        $res2 = $this->getJson('/api/v1/products?category=indoor-plants&sort=popular');
        $res2->assertStatus(200)->assertJsonPath('success', true);
        $this->assertNotEmpty($res2->json('data'));

        // Price low sort
        $res3 = $this->getJson('/api/v1/products?sort=price_low');
        $res3->assertStatus(200)->assertJsonPath('success', true);
        $this->assertNotEmpty($res3->json('data'));
    }

    public function test_order_creation_persists_phone_and_address_to_authenticated_user_profile(): void
    {
        $user = \App\Models\User::factory()->create([
            'phone' => null,
            'street_address' => null,
            'city' => null,
            'state' => null,
            'postal_code' => null,
            'cart' => [
                ['id' => '1-10', 'variantId' => 10, 'quantity' => 1]
            ],
        ]);

        $variant = ProductVariant::first();
        $this->assertNotNull($variant);

        $orderPayload = [
            'customer_name' => 'Botanical Enthusiast',
            'customer_email' => $user->email,
            'customer_phone' => '+91 9988776655',
            'shipping_address' => [
                'street' => '12 Palm Grove Villa, 4th Cross',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'postal_code' => '560034',
            ],
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'quantity' => 1,
                ],
            ],
            'payment_method' => 'cod',
        ];

        $res = $this->actingAs($user)->postJson('/api/v1/checkout/orders', $orderPayload);
        $res->assertStatus(201)->assertJsonPath('success', true);

        $user->refresh();
        $this->assertEquals('+91 9988776655', $user->phone);
        $this->assertEquals('12 Palm Grove Villa, 4th Cross', $user->street_address);
        $this->assertEquals('Bengaluru', $user->city);
        $this->assertEquals('Karnataka', $user->state);
        $this->assertEquals('560034', $user->postal_code);
        $this->assertEquals([], $user->cart);

        // Test explicit profile update endpoint
        $updateRes = $this->actingAs($user)->putJson('/api/v1/auth/profile', [
            'phone' => '+91 9123456780',
            'street_address' => '45 Green Orchid Lane',
            'city' => 'Mysuru',
            'state' => 'Karnataka',
            'postal_code' => '570001',
        ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.phone', '+91 9123456780')
            ->assertJsonPath('user.street_address', '45 Green Orchid Lane')
            ->assertJsonPath('user.city', 'Mysuru')
            ->assertJsonPath('user.postal_code', '570001');
    }

    public function test_cart_and_wishlist_cross_device_sync(): void
    {
        $user = User::factory()->create([
            'email' => 'sync-test@nursery.test',
            'cart' => [
                ['id' => '1-10', 'variantId' => 10, 'quantity' => 2, 'title' => 'Fiddle Leaf Fig']
            ],
            'wishlist' => [
                ['id' => 1, 'name' => 'Fiddle Leaf Fig']
            ],
        ]);

        // 1. Sync cart: merging a new item from another device
        $cartSyncRes = $this->actingAs($user)->postJson('/api/v1/auth/cart/sync', [
            'merge' => true,
            'items' => [
                ['id' => '2-20', 'variantId' => 20, 'quantity' => 1, 'title' => 'Monstera Deliciosa']
            ]
        ]);

        $cartSyncRes->assertStatus(200)
            ->assertJsonPath('success', true);
        
        $user->refresh();
        $this->assertCount(2, $user->cart);

        // 2. Sync wishlist: merging items
        $wishlistSyncRes = $this->actingAs($user)->postJson('/api/v1/auth/wishlist/sync', [
            'merge' => true,
            'items' => [
                ['id' => 2, 'name' => 'Monstera Deliciosa']
            ]
        ]);

        $wishlistSyncRes->assertStatus(200)
            ->assertJsonPath('success', true);

        $user->refresh();
        $this->assertCount(2, $user->wishlist);

        // 3. User profile includes cart and wishlist
        $profileRes = $this->actingAs($user)->getJson('/api/v1/auth/user');
        $profileRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'user.cart')
            ->assertJsonCount(2, 'user.wishlist');
    }

    public function test_state_based_delivery_charges_in_checkout_and_orders(): void
    {
        // 1. Configure state shipping rates
        \App\Models\SiteSetting::set('enable_state_shipping', true, 'shipping', 'boolean');
        \App\Models\SiteSetting::set('free_shipping_threshold', 1000.00, 'shipping', 'number');
        \App\Models\SiteSetting::set('default_shipping_fee', 80.00, 'shipping', 'number');
        \App\Models\SiteSetting::set('state_shipping_rates', [
            ['state' => 'Karnataka', 'fee' => 49.00, 'estimated_days' => '1-2 days'],
            ['state' => 'Maharashtra', 'fee' => 89.00, 'estimated_days' => '3-4 days'],
            ['state' => 'Delhi', 'fee' => 99.00, 'estimated_days' => '3-4 days'],
        ], 'shipping', 'json');

        $variant = ProductVariant::first();

        // 2. Checkout summary for Karnataka (should charge 49)
        $karnatakaSummary = $this->postJson('/api/v1/checkout/summary', [
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => 1],
            ],
            'state' => 'Karnataka',
            'postal_code' => '560001',
        ]);

        $karnatakaSummary->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('breakdown.shipping', 49)
            ->assertJsonPath('breakdown.is_state_rate_applied', true)
            ->assertJsonPath('breakdown.estimated_transit_days', '1-2 days');

        // 3. Checkout summary for Maharashtra (should charge 89)
        $mhSummary = $this->postJson('/api/v1/checkout/summary', [
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => 1],
            ],
            'state' => 'Maharashtra',
            'postal_code' => '400001',
        ]);

        $mhSummary->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('breakdown.shipping', 89)
            ->assertJsonPath('breakdown.is_state_rate_applied', true)
            ->assertJsonPath('breakdown.estimated_transit_days', '3-4 days');

        // 4. Checkout summary for an unlisted state (falls back to default 80.00)
        $unlistedSummary = $this->postJson('/api/v1/checkout/summary', [
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => 1],
            ],
            'state' => 'Random State',
            'postal_code' => '500001',
        ]);

        $unlistedSummary->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('breakdown.shipping', 80)
            ->assertJsonPath('breakdown.is_state_rate_applied', false);

        // 5. Order creation with destination state Karnataka persists shipping_fee = 49
        $orderRes = $this->postJson('/api/v1/checkout/orders', [
            'customer_name' => 'Botanical Fan',
            'customer_email' => 'fan@nursery.test',
            'customer_phone' => '+91 9988776655',
            'shipping_address' => [
                'street' => '10 Cubbon Park Road',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'postal_code' => '560001',
            ],
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => 1],
            ],
            'payment_method' => 'cod',
        ]);

        $orderRes->assertStatus(201)
            ->assertJsonPath('success', true);

        $orderNumber = $orderRes->json('order.order_number');
        $order = Order::where('order_number', $orderNumber)->first();
        $this->assertEquals(49.00, (float) $order->shipping_fee);
    }
}


