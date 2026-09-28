<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_customer_can_register_with_email_and_password(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Meera Patel',
            'email' => 'meera.patel@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'user' => [
                'name' => 'Meera Patel',
                'email' => 'meera.patel@example.com',
                'is_admin' => false,
            ],
        ]);
        $this->assertNotEmpty($response->json('token'));

        $this->assertDatabaseHas('users', [
            'email' => 'meera.patel@example.com',
            'name' => 'Meera Patel',
            'is_admin' => false,
        ]);
    }

    public function test_customer_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'name' => 'Kavita Rao',
            'email' => 'kavita@nursery.test',
            'password' => bcrypt('botanical2026'),
            'is_admin' => false,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'kavita@nursery.test',
            'password' => 'botanical2026',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'user' => [
                'email' => 'kavita@nursery.test',
            ],
        ]);
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_customer_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@nursery.test',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }

    public function test_customer_can_sign_in_via_google_social_login(): void
    {
        $response = $this->postJson('/api/v1/auth/social', [
            'provider' => 'google',
            'provider_id' => 'google-oauth2-1092837465',
            'email' => 'rohit.sharma@gmail.com',
            'name' => 'Rohit Sharma',
            'avatar' => 'https://lh3.googleusercontent.com/a/sample-avatar',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'user' => [
                'email' => 'rohit.sharma@gmail.com',
                'name' => 'Rohit Sharma',
                'provider' => 'google',
                'avatar' => 'https://lh3.googleusercontent.com/a/sample-avatar',
            ],
        ]);
        $this->assertNotEmpty($response->json('token'));

        $this->assertDatabaseHas('users', [
            'email' => 'rohit.sharma@gmail.com',
            'provider' => 'google',
            'provider_id' => 'google-oauth2-1092837465',
        ]);
    }

    public function test_customer_can_sign_in_via_github_social_login(): void
    {
        $response = $this->postJson('/api/v1/auth/social', [
            'provider' => 'github',
            'provider_id' => 'gh-8823491',
            'email' => 'priya.dev@github.com',
            'name' => 'Priya Developer',
            'avatar' => 'https://avatars.githubusercontent.com/u/8823491',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'user' => [
                'email' => 'priya.dev@github.com',
                'provider' => 'github',
            ],
        ]);
    }

    public function test_authenticated_customer_can_fetch_profile_and_orders(): void
    {
        $user = User::factory()->create([
            'name' => 'Arun Kumar',
            'email' => 'arun@example.com',
            'is_admin' => false,
            'is_active' => true,
        ]);

        // Create an order for this customer
        Order::create([
            'order_number' => 'NUR-ARUN123',
            'user_id' => $user->id,
            'customer_name' => 'Arun Kumar',
            'customer_email' => 'arun@example.com',
            'status' => 'transit',
            'subtotal' => 85.00,
            'total_amount' => 85.00,
            'shipping_address' => [
                'street' => '12 MG Road',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'postal_code' => '560001',
            ],
        ]);

        // 1. Fetch user profile
        $userRes = $this->actingAs($user, 'sanctum')->getJson('/api/v1/auth/user');
        $userRes->assertStatus(200);
        $userRes->assertJson([
            'success' => true,
            'user' => [
                'email' => 'arun@example.com',
                'orders_count' => 1,
            ],
        ]);

        // 2. Fetch orders
        $ordersRes = $this->actingAs($user, 'sanctum')->getJson('/api/v1/auth/orders');
        $ordersRes->assertStatus(200);
        $ordersRes->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'order_number', 'status', 'total_amount'],
            ],
        ]);
        $this->assertEquals('NUR-ARUN123', $ordersRes->json('data.0.order_number'));
    }

    public function test_customer_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_customer_can_request_password_reset_link(): void
    {
        $user = User::factory()->create([
            'email' => 'resetme@nursery.test',
        ]);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'resetme@nursery.test',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_customer_forgot_password_returns_generic_success_for_nonexistent_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'unknown@nursery.test',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
}
