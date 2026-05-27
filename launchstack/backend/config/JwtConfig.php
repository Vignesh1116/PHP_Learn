<?php

declare(strict_types=1);

/**
 * LaunchStack — JWT Configuration
 */

namespace LaunchStack\Config;

class JwtConfig
{
    public static function getSecret(): string
    {
        $secret = $_ENV['JWT_SECRET'] ?? '';
        if (empty($secret) || strlen($secret) < 32) {
            throw new \RuntimeException('JWT_SECRET must be at least 32 characters long.');
        }
        return $secret;
    }

    public static function getAlgorithm(): string
    {
        return $_ENV['JWT_ALGORITHM'] ?? 'HS256';
    }

    public static function getAccessExpiry(): int
    {
        return (int) ($_ENV['JWT_ACCESS_EXPIRY'] ?? 900); // 15 minutes
    }

    public static function getRefreshExpiry(): int
    {
        return (int) ($_ENV['JWT_REFRESH_EXPIRY'] ?? 2592000); // 30 days
    }
}
