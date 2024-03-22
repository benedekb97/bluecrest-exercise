<?php

declare(strict_types=1);

namespace Tests\Feature;

use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public const USER_PASSWORD = 'password';
    public const USER_EMAIL = 'test@example.com';

    public const USER_CREDENTIALS = [
        'email' => self::USER_EMAIL,
        'password' => self::USER_PASSWORD,
    ];

    public function testUserLogin(): void
    {
        $response = $this->json('POST', '/api/auth/login', self::USER_CREDENTIALS);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseBody = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('access_token', $responseBody);
        $this->assertArrayHasKey('token_type', $responseBody);
        $this->assertArrayHasKey('expires_in', $responseBody);

        $this->assertNotNull($responseBody['access_token']);
    }

    public function testFetchUserData(): void
    {
        $this->authenticate();

        $this->sendGet('/api/auth/me');
        $this->assertStatusOk();

        $response = $this->getResponseArray();

        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('email', $response);
        $this->assertArrayHasKey('name', $response);
        $this->assertArrayNotHasKey('password', $response);
    }

    public function testRefreshBearer(): void
    {
        $this->authenticate();

        $this->sendJsonPost('/api/auth/refresh');

        $this->assertStatusOk();

        $responseBody = $this->getResponseArray();

        $this->assertArrayHasKey('access_token', $responseBody);
        $this->assertArrayHasKey('token_type', $responseBody);
        $this->assertArrayHasKey('expires_in', $responseBody);
    }

    public function testLogout(): void
    {
        $this->authenticate();

        $this->sendJsonPost('/api/auth/logout');

        $this->assertStatusOk();
    }
}
