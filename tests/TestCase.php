<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Auth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function actingAsUser()
    {
        // every generated e-mail will be accepted
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');
        Auth::setUser($user);

        return $this;
    }
}
