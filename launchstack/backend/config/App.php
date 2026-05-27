<?php

declare(strict_types=1);

/**
 * LaunchStack — Application Bootstrap
 * Central app configuration and initialization
 */

namespace LaunchStack\Config;

use Dotenv\Dotenv;

class App
{
    private static bool $initialized = false;

    public static function bootstrap(string $basePath): void
    {
        if (self::$initialized) {
            return;
        }

        // Load environment variables
        if (file_exists($basePath . '/.env')) {
            $dotenv = Dotenv::createImmutable($basePath);
            $dotenv->load();
        }

        // Set PHP configuration
        ini_set('display_errors', $_ENV['APP_DEBUG'] === 'true' ? '1' : '0');
        error_reporting(E_ALL);

        // Set default timezone
        date_default_timezone_set('UTC');

        // Security headers
        self::setSecurityHeaders();

        self::$initialized = true;
    }

    private static function setSecurityHeaders(): void
    {
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        }
    }

    public static function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }

    public static function isDebug(): bool
    {
        return ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
    }

    public static function isDevelopment(): bool
    {
        return ($_ENV['APP_ENV'] ?? 'production') === 'development';
    }
}
