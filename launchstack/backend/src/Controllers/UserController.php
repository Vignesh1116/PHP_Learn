<?php

declare(strict_types=1);

/**
 * LaunchStack — User Controller
 * Handles user profile and account management
 */

namespace LaunchStack\Controllers;

use LaunchStack\Models\User;
use LaunchStack\Models\RefreshToken;
use LaunchStack\Helpers\Response;
use LaunchStack\Helpers\Validator;
use LaunchStack\Middleware\JwtMiddleware;

class UserController
{
    private User $userModel;
    private RefreshToken $refreshTokenModel;
    private JwtMiddleware $jwtMiddleware;

    public function __construct()
    {
        $this->userModel         = new User();
        $this->refreshTokenModel = new RefreshToken();
        $this->jwtMiddleware     = new JwtMiddleware();
    }

    /**
     * GET /api/user/profile
     * Returns the authenticated user's profile
     */
    public function profile(): never
    {
        $payload = $this->jwtMiddleware->handle();
        $user    = $this->userModel->findById((int) $payload->sub);

        if (!$user) {
            Response::notFound('User not found.');
        }

        Response::success([
            'user'            => $user,
            'active_sessions' => $this->refreshTokenModel->countActiveSessions((int) $payload->sub),
        ], 'Profile retrieved successfully.');
    }

    /**
     * PATCH /api/user/profile
     * Update authenticated user's name
     */
    public function updateProfile(): never
    {
        $payload = $this->jwtMiddleware->handle();
        $body    = Validator::parseJsonBody();

        $validator = Validator::make($body, [
            'name' => 'required|string|min:2|max:100',
        ]);

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        $validated = $validator->validated();
        $this->userModel->updateProfile((int) $payload->sub, ['name' => $validated['name']]);

        $user = $this->userModel->findById((int) $payload->sub);
        Response::success(['user' => $user], 'Profile updated successfully.');
    }

    /**
     * POST /api/user/change-password
     */
    public function changePassword(): never
    {
        $payload = $this->jwtMiddleware->handle();
        $body    = Validator::parseJsonBody();

        $validator = Validator::make($body, [
            'current_password' => 'required|min:1',
            'new_password'     => 'required|min:8|max:128|confirmed',
            'new_password_confirmation' => 'required',
        ]);

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        // Fetch full user record to check password
        $userFull = $this->userModel->findByEmail($payload->email ?? '');
        if (!$userFull || !password_verify($body['current_password'], $userFull['password_hash'])) {
            Response::error('Current password is incorrect.', 401);
        }

        $newHash = password_hash($body['new_password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $this->userModel->updatePassword((int) $payload->sub, $newHash);

        // Revoke all refresh tokens for security
        $this->refreshTokenModel->revokeAllForUser((int) $payload->sub);

        Response::success(
            null,
            'Password changed successfully. Please log in again with your new password.'
        );
    }

    /**
     * GET /api/admin/users
     * Admin only: list all users with pagination
     */
    public function listUsers(): never
    {
        $payload = $this->jwtMiddleware->handle();
        $this->jwtMiddleware->requireAdmin($payload);

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($_GET['per_page'] ?? 20)));

        $result = $this->userModel->getAllPaginated($page, $perPage);

        Response::paginated(
            $result['users'],
            $result['total'],
            $page,
            $perPage,
            'Users retrieved successfully.'
        );
    }
}
