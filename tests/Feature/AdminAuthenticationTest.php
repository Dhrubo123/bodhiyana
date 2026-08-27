<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_endpoints_require_authentication(): void
    {
        $this->getJson('/api/admin/dashboard')->assertUnauthorized();
    }

    public function test_admin_can_login_and_logout_with_a_session(): void
    {
        User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ]);

        $this->postJson('/api/admin/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ])->assertOk()->assertJsonPath('user.email', 'admin@example.test');

        $this->getJson('/api/admin/dashboard')->assertOk();
        $this->postJson('/api/admin/logout')->assertOk();
        $this->getJson('/api/admin/dashboard')->assertUnauthorized();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create(['email' => 'admin@example.test']);

        $this->postJson('/api/admin/login', [
            'email' => 'admin@example.test',
            'password' => 'wrong-password',
        ])->assertUnprocessable();
    }
}
