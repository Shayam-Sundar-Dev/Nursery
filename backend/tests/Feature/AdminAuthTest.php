<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_non_admin_user_is_forbidden_from_admin_panel(): void
    {
        $shopper = User::where('email', 'gardener@nursery.test')->first();

        $response = $this->actingAs($shopper)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_dashboard(): void
    {
        $admin = User::where('email', 'admin@nursery.test')->first();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Botanical Operations Dashboard');
        $response->assertSee('Verdant Flora');
    }

    public function test_admin_can_login_via_web_form(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@nursery.test',
            'password' => 'admin1234',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    public function test_non_admin_cannot_login_via_admin_form(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'gardener@nursery.test',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_logout(): void
    {
        $admin = User::where('email', 'admin@nursery.test')->first();

        $response = $this->actingAs($admin)->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_admin_api_login_returns_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@nursery.test',
            'password' => 'admin1234',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'name', 'email', 'is_admin'],
                    'token',
                    'token_type',
                ],
            ]);
    }

    public function test_non_admin_cannot_authenticate_via_admin_api(): void
    {
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'gardener@nursery.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }
}
