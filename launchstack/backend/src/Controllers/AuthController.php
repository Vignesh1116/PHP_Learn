<?php

declare(strict_types=1);

/**
 * LaunchStack — Auth Controller
 * Handles all authentication HTTP endpoints
 */

namespace LaunchStack\Controllers;

use LaunchStack\Services\AuthService;
use LaunchStack\Helpers\Response;
use LaunchStack\Helpers\Validator;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * POST /api/auth/register
     */
    public function register(): never
    {
        $body = Validator::parseJsonBody();

        $validator = Validator::make($body, [
            'name'                  => 'required|string|min:2|max:100',
            'email'                 => 'required|email|max:255',
            'password'              => 'required|min:8|max:128|confirmed',
            'password_confirmation' => 'required',
        ]);

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        $validated = $validator->validated();

        try {
            $result = $this->authService->register([
                'name'     => $validated['name'],
                'email'    => strtolower($validated['email']),
                'password' => $validated['password'],
            ]);

            Response::success($result, 'Registration successful!', 201);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * POST /api/auth/login
     */
    public function login(): never
    {
        $body = Validator::parseJsonBody();

        $validator = Validator::make($body, [
            'email'    => 'required|email',
            'password' => 'required|min:1',
        ]);

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '';

        try {
            $result = $this->authService->login([
                'email'    => strtolower(trim($body['email'])),
                'password' => $body['password'],
            ], $ipAddress);

            Response::success($result, 'Login successful!');
        } catch (\RuntimeException $e) {
            // Return same error for wrong email/password to prevent user enumeration
            $code = $e->getCode() ?: 401;
            Response::error($e->getMessage(), $code);
        }
    }

    /**
     * POST /api/auth/refresh
     */
    public function refresh(): never
    {
        $body = Validator::parseJsonBody();

        if (empty($body['refresh_token'])) {
            Response::error('refresh_token is required.', 400);
        }

        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '';

        try {
            $result = $this->authService->refreshToken($body['refresh_token'], $ipAddress);
            Response::success($result, 'Token refreshed successfully.');
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 401);
        }
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(): never
    {
        $body = Validator::parseJsonBody();

        if (empty($body['refresh_token'])) {
            Response::error('refresh_token is required to logout.', 400);
        }

        $this->authService->logout($body['refresh_token']);

        Response::success(null, 'Logged out successfully.');
    }

    /**
     * POST /api/auth/logout-all
     * Requires JWT auth — see Router
     */
    public function logoutAll(object $authPayload): never
    {
        $userId = (int) $authPayload->sub;
        $this->authService->logoutAll($userId);
        Response::success(null, 'Logged out from all devices successfully.');
    }

    /**
     * GET /api/auth/verify-email?token=xxx
     */
    public function verifyEmail(): never
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            Response::error('Verification token is required.', 400);
        }

        try {
            $result = $this->authService->verifyEmail($token);
            Response::success($result, $result['message']);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    /**
     * POST /api/auth/resend-verification
     */
    public function resendVerification(): never
    {
        $body = Validator::parseJsonBody();

        $validator = Validator::make($body, [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        // For security, always return success even if email not found
        try {
            $this->authService->resendVerification(strtolower(trim($body['email'])));
        } catch (\Exception $e) {
            // Silently fail
        }

        Response::success(
            null,
            'If an account with that email exists, a verification email has been sent.'
        );
    }
}
