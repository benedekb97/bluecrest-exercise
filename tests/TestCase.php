<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\AuthenticationTest;

abstract class TestCase extends BaseTestCase
{
    private ?TestResponse $response = null;

    private ?string $bearer = null;

    protected function sendJsonPost(string $url, array $data = [], array $headers = []): void
    {
        $this->mergeAuthenticationHeaders($headers);

        $this->response = $this->json('POST', $url, $data, $headers);
    }

    protected function sendGet(string $url, array $headers = []): void
    {
        $this->mergeAuthenticationHeaders($headers);

        $this->response = $this->json('GET', $url, $headers);
    }

    protected function sendJsonPatch(string $url, array $data = [], array $headers = []): void
    {
        $this->mergeAuthenticationHeaders($headers);

        $this->response = $this->json('PATCH', $url, $data, $headers);
    }

    protected function sendDelete(string $url, array $data = [], array $headers = []): void
    {
        $this->mergeAuthenticationHeaders($headers);

        $this->response = $this->json('DELETE', $url, $data, $headers);
    }

    private function mergeAuthenticationHeaders(array & $headers): void
    {
        if (isset($this->bearer)) {
            $headers = array_merge(['HTTP_AUTHORIZATION' => 'Bearer ' . $this->bearer], $headers);
        }
    }

    protected function getResponseArray(): ?array
    {
        return json_decode($this->response->getContent(), true);
    }

    protected function authenticate(): void
    {
        $this->sendJsonPost('/api/auth/login', AuthenticationTest::USER_CREDENTIALS);

        $this->bearer = $this->response['access_token'];
    }

    protected function assertStatusOk(): void
    {
        $this->assertEquals(Response::HTTP_OK, $this->response?->getStatusCode());
    }

    protected function assertStatusCreated(): void
    {
        $this->assertEquals(Response::HTTP_CREATED, $this->response?->getStatusCode());
    }

    protected function assertStatusUnprocessable(): void
    {
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->response?->getStatusCode());
    }

    protected function assertStatusUnauthenticated(): void
    {
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->response?->getStatusCode());
    }

    protected function assertStatusNoContent(): void
    {
        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->response?->getStatusCode());
    }

    protected function assertStatusNotFound(): void
    {
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->response?->getStatusCode());
    }

    protected function assertRedirect(): void
    {
        $this->assertEquals(Response::HTTP_FOUND, $this->response?->getStatusCode());
    }
}
