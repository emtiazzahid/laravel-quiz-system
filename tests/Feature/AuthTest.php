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

        $response = $this->json('POST', '/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'access_token', 'token_type'
            ]);
    }

    /**
     * Test logout.
     *
     * @return void
     */
    public function testLogout()
    {
        $response = $this->actingAsUser()
            ->json('POST', '/api/auth/logout', []);

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
        $response = $this->actingAsUser()->json('POST', '/api/auth/refresh', []);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'access_token', 'token_type'
            ]);
    }

    /**
     * Get all users.
     *
     * @return void
     */
    public function testGetUsers()
    {
        $response = $this->actingAsUser()->json('GET', '/api/quiz', []);

        $response->assertStatus(200);
    }
}
