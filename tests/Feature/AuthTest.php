<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use JWTAuth;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Login as default API user and get token back.
     *
     * @return void
     */
    public function testLogin()
    {
        $user = User::factory()->create();

        $baseUrl = Config::get('app.url') . '/api/auth/login';

        $response = $this->json('POST', $baseUrl . '/', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'access_token', 'token_type', 'expires_in'
            ]);
    }

    /**
     * Test logout.
     *
     * @return void
     */
    public function testLogout()
    {
        $baseUrl = Config::get('app.url') . '/api/auth/logout';

        $response = $this->actingAsUser()
            ->json('POST', $baseUrl, []);

        $response->assertStatus(200)
            ->assertExactJson([
                'message' => 'User successfully signed out'
            ]);
    }

    /**
     * Test token refresh.
     *
     * @return void
     */
    public function testRefresh()
    {
        $baseUrl = Config::get('app.url') . '/api/auth/refresh';

        $response = $this->actingAsUser()->json('POST', $baseUrl, []);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'access_token', 'token_type', 'expires_in'
            ]);
    }

    /**
     * Get all users.
     *
     * @return void
     */
    public function testGetUsers()
    {
        $baseUrl = Config::get('app.url') . '/api/quiz';

        $response = $this->actingAsUser()->json('GET', $baseUrl . '/', []);

        $response->assertStatus(200);
    }
}
