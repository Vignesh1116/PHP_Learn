<?php

declare(strict_types=1);

/**
 * LaunchStack — CORS Middleware
 * Handles Cross-Origin Resource Sharing for React frontend
 */

namespace LaunchStack\Middleware;

class CorsMiddleware
{
    public static function handle(): void
    {
        $allowedOrigins = [
            $_ENV['FRONTEND_URL'] ?? 'http://localhost:5173',
            'http://localhost:3000',
            'http://localhost:5173',
        ];

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (in_array($origin, $allowedOrigins, true)) {
            header("Access-Control-Allow-Origin: {$origin}");
        } elseif (($_ENV['APP_ENV'] ?? 'production') === 'development') {
            // In development, allow all origins
            header('Access-Control-Allow-Origin: *');
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // Cache preflight for 24h

        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
