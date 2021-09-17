<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function actingAsUser()
    {
        $password = 'password';

        // every generated e-mail will be accepted
        $user = User::factory()->create();

        $token = auth('api')->attempt([
            'email' => $user->email,
            'password' => $password
        ]);

        $this->withHeaders(
            array_merge([
                $this->defaultHeaders,
                ['Authorization' => 'Bearer ' . $token]
            ])
        );

        return $this;
    }
}
