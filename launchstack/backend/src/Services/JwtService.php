<?php

declare(strict_types=1);

/**
 * LaunchStack — JWT Service
 * Handles JWT access token + refresh token lifecycle
 */

namespace LaunchStack\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use LaunchStack\Config\JwtConfig;

class JwtService
{
    /**
     * Generate an access token (short-lived: 15 minutes)
     */
    public function generateAccessToken(array $payload): string
    {
        $now = time();
        $claims = array_merge($payload, [
            'iss'  => $_ENV['APP_URL'] ?? 'http://localhost:8000',
            'aud'  => 'launchstack-api',
            'iat'  => $now,
            'nbf'  => $now,
            'exp'  => $now + JwtConfig::getAccessExpiry(),
            'type' => 'access',
        ]);

        return JWT::encode($claims, JwtConfig::getSecret(), JwtConfig::getAlgorithm());
    }

    /**
     * Generate a refresh token (long-lived: 30 days)
     */
    public function generateRefreshToken(int $userId): string
    {
        $now = time();
        $claims = [
            'iss'     => $_ENV['APP_URL'] ?? 'http://localhost:8000',
            'iat'     => $now,
            'exp'     => $now + JwtConfig::getRefreshExpiry(),
            'sub'     => $userId,
            'type'    => 'refresh',
            'jti'     => bin2hex(random_bytes(32)), // Unique token ID
        ];

        return JWT::encode($claims, JwtConfig::getSecret(), JwtConfig::getAlgorithm());
    }

    /**
     * Validate and decode a JWT token
     * Returns decoded payload or throws exception
     */
    public function validateToken(string $token): object
    {
        try {
            $decoded = JWT::decode(
                $token,
                new Key(JwtConfig::getSecret(), JwtConfig::getAlgorithm())
            );
            return $decoded;
        } catch (ExpiredException $e) {
            throw new \RuntimeException('Token has expired. Please login again.', 401);
        } catch (SignatureInvalidException $e) {
            throw new \RuntimeException('Invalid token signature.', 401);
        } catch (\Exception $e) {
            throw new \RuntimeException('Invalid or malformed token.', 401);
        }
    }

    /**
     * Extract Bearer token from Authorization header
     */
    public function extractBearerToken(): ?string
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (empty($authHeader)) {
            // Some servers use REDIRECT_HTTP_AUTHORIZATION
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        }

        if (preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Get token payload without validation (for debugging only)
     */
    public function decodeWithoutValidation(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
        return json_decode($payload, true);
    }
}
