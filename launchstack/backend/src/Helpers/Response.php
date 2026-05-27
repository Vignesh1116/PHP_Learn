<?php

declare(strict_types=1);

/**
 * LaunchStack — HTTP Response Helper
 * Standardized API response format across all endpoints
 */

namespace LaunchStack\Helpers;

class Response
{
    /**
     * Send a success response
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): never {
        self::send([
            'status'    => 'success',
            'message'   => $message,
            'data'      => $data,
            'timestamp' => date('c'),
        ], $statusCode);
    }

    /**
     * Send an error response
     */
    public static function error(
        string $message = 'An error occurred',
        int $statusCode = 400,
        mixed $errors = null
    ): never {
        $body = [
            'status'    => 'error',
            'message'   => $message,
            'timestamp' => date('c'),
        ];

        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        self::send($body, $statusCode);
    }

    /**
     * Send a 401 Unauthorized response
     */
    public static function unauthorized(string $message = 'Unauthorized. Please authenticate.'): never
    {
        self::error($message, 401);
    }

    /**
     * Send a 403 Forbidden response
     */
    public static function forbidden(string $message = 'Access denied.'): never
    {
        self::error($message, 403);
    }

    /**
     * Send a 404 Not Found response
     */
    public static function notFound(string $message = 'Resource not found.'): never
    {
        self::error($message, 404);
    }

    /**
     * Send a 422 Validation Error response
     */
    public static function validationError(array $errors): never
    {
        self::error('Validation failed. Please check your input.', 422, $errors);
    }

    /**
     * Send a 429 Rate Limit response
     */
    public static function rateLimited(int $retryAfter = 60): never
    {
        if (!headers_sent()) {
            header("Retry-After: {$retryAfter}");
        }
        self::error('Too many requests. Please slow down.', 429);
    }

    /**
     * Core send method
     */
    private static function send(array $body, int $statusCode): never
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=UTF-8');
        }

        echo json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send paginated response
     */
    public static function paginated(
        array $data,
        int $total,
        int $page,
        int $perPage,
        string $message = 'Success'
    ): never {
        self::send([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
            'meta'    => [
                'total'        => $total,
                'page'         => $page,
                'per_page'     => $perPage,
                'total_pages'  => (int) ceil($total / $perPage),
                'has_next'     => ($page * $perPage) < $total,
                'has_prev'     => $page > 1,
            ],
            'timestamp' => date('c'),
        ], 200);
    }
}
