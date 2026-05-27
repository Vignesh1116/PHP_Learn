<?php

declare(strict_types=1);

/**
 * LaunchStack — JWT Middleware
 * Protects routes by validating Bearer tokens
 */

namespace LaunchStack\Middleware;

use LaunchStack\Services\JwtService;
use LaunchStack\Helpers\Response;

class JwtMiddleware
{
    private JwtService $jwtService;

    public function __construct()
    {
        $this->jwtService = new JwtService();
    }

    /**
     * Validate the Bearer token and inject user data into request context
     * Returns decoded payload on success, exits with error response on failure
     */
    public function handle(): object
    {
        $token = $this->jwtService->extractBearerToken();

        if ($token === null) {
            Response::unauthorized('No authentication token provided. Include: Authorization: Bearer <token>');
        }

        try {
            $payload = $this->jwtService->validateToken($token);
        } catch (\RuntimeException $e) {
            Response::unauthorized($e->getMessage());
        }

        // Ensure it's an access token, not a refresh token
        if (($payload->type ?? '') !== 'access') {
            Response::unauthorized('Invalid token type. Use an access token.');
        }

        return $payload;
    }

    /**
     * Require admin role
     */
    public function requireAdmin(object $payload): void
    {
        if (($payload->role ?? '') !== 'admin') {
            Response::forbidden('Admin access required.');
        }
    }

    /**
     * Require a specific subscription plan
     */
    public function requirePlan(object $payload, array $allowedPlans): void
    {
        if (!in_array($payload->plan ?? 'free', $allowedPlans, true)) {
            Response::forbidden(
                'Your current plan (' . ($payload->plan ?? 'free') . ') does not have access to this feature. '
                . 'Please upgrade to: ' . implode(' or ', $allowedPlans)
            );
        }
    }
}
