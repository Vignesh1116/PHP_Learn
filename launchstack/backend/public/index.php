<?php

declare(strict_types=1);

/**
 * LaunchStack — Application Entry Point
 * All HTTP requests are routed through public/index.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use LaunchStack\Config\App;
use LaunchStack\Middleware\CorsMiddleware;
use LaunchStack\Router;
use LaunchStack\Controllers\AuthController;
use LaunchStack\Controllers\UserController;
use LaunchStack\Helpers\Response;

// ─── Bootstrap ───────────────────────────────────────────────────────────────
App::bootstrap(__DIR__ . '/..');

// ─── CORS ────────────────────────────────────────────────────────────────────
CorsMiddleware::handle();

// ─── Router ──────────────────────────────────────────────────────────────────
$router = new Router();

// ── Root Route ───────────────────────────────────────────────────────────────
$router->get('/', function () {
    Response::success([
        'app'     => 'LaunchStack API',
        'version' => $_ENV['APP_VERSION'] ?? '1.0.0',
    ], 'Welcome to LaunchStack API');
});

// ── Health Check ──────────────────────────────────────────────────────────────
$router->get('/api/health', function () {
    Response::success([
        'status'    => 'operational',
        'version'   => $_ENV['APP_VERSION'] ?? '1.0.0',
        'timestamp' => date('c'),
        'uptime'    => 'OK',
    ], 'LaunchStack API is running 🚀');
});

// ── Auth Routes ───────────────────────────────────────────────────────────────
$router->post('/api/auth/register',             [AuthController::class, 'register']);
$router->post('/api/auth/login',                [AuthController::class, 'login']);
$router->post('/api/auth/refresh',              [AuthController::class, 'refresh']);
$router->post('/api/auth/logout',               [AuthController::class, 'logout']);
$router->post('/api/auth/resend-verification',  [AuthController::class, 'resendVerification']);
$router->get('/api/auth/verify-email',          [AuthController::class, 'verifyEmail']);

// ── Protected User Routes (require JWT) ───────────────────────────────────────
$router->get('/api/user/profile',               [UserController::class, 'profile']);
$router->patch('/api/user/profile',             [UserController::class, 'updateProfile']);
$router->post('/api/user/change-password',      [UserController::class, 'changePassword']);
$router->post('/api/auth/logout-all',           [UserController::class, 'logoutAll']);

// ── Admin Routes (require JWT + admin role) ───────────────────────────────────
$router->get('/api/admin/users',                [UserController::class, 'listUsers']);

// ─── Dispatch ────────────────────────────────────────────────────────────────
try {
    $router->dispatch();
} catch (\Throwable $e) {
    $isDebug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

    if ($isDebug) {
        Response::error($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), 500);
    } else {
        error_log('[LaunchStack] Unhandled error: ' . $e->getMessage());
        Response::error('An internal server error occurred. Please try again later.', 500);
    }
}
