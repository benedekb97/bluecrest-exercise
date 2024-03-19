<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Factory;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationController extends Controller
{
    public function __construct(
        private Factory $auth
    ) {}

    public function login(Request $request): Response
    {
        $credentials = [
            'email' => $request->get('email'),
            'password' => $request->get('password'),
        ];

        if (!$token = $this->auth->guard('api')->attempt($credentials)) {
            return new JsonResponse(
                [
                    'message' => 'Unauthorized',
                ],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return $this->respondWithToken($token);
    }

    public function logout(): Response
    {
        $this->auth->guard('api')->logout();

        return new JsonResponse(
            [
                'message' => 'Logged out successfully.',
            ],
            Response::HTTP_OK
        );
    }

    public function me(): Response
    {
        return new JsonResponse(
            $this->auth->guard('api')->user()
        );
    }

    public function refresh(): Response
    {
        return $this->respondWithToken(
            $this->auth->guard('api')->refresh()
        );
    }

    private function respondWithToken(string $token): JsonResponse
    {
        return new JsonResponse(
            [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => 60 * 60,
            ]
        );
    }
}
