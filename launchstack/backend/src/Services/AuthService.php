<?php

declare(strict_types=1);

/**
 * LaunchStack — Auth Service
 * Core authentication business logic
 */

namespace LaunchStack\Services;

use LaunchStack\Models\User;
use LaunchStack\Models\RefreshToken;
use LaunchStack\Models\EmailVerification;

class AuthService
{
    private User $userModel;
    private RefreshToken $refreshTokenModel;
    private EmailVerification $emailVerificationModel;
    private JwtService $jwtService;
    private EmailService $emailService;

    public function __construct()
    {
        $this->userModel              = new User();
        $this->refreshTokenModel      = new RefreshToken();
        $this->emailVerificationModel = new EmailVerification();
        $this->jwtService             = new JwtService();
        $this->emailService           = new EmailService();
    }

    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        // Check for duplicate email
        if ($this->userModel->emailExists($data['email'])) {
            throw new \RuntimeException('An account with this email already exists.', 409);
        }

        // Hash password with bcrypt (cost 12)
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        // Create the user
        $userId = $this->userModel->create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password_hash' => $passwordHash,
            'role'          => 'user',
            'plan'          => 'free',
        ]);

        // Generate email verification token
        $verificationToken = $this->emailVerificationModel->create($userId, $data['email']);

        // Send verification email (non-blocking)
        try {
            $this->emailService->sendVerificationEmail($data['email'], $data['name'], $verificationToken);
        } catch (\Exception $e) {
            // Don't fail registration if email fails — log it
            error_log('[LaunchStack Auth] Email send failed for user ' . $userId . ': ' . $e->getMessage());
        }

        $user = $this->userModel->findById($userId);

        return [
            'user'              => $this->sanitizeUser($user),
            'verification_sent' => true,
            'message'           => 'Registration successful! Please check your email to verify your account.',
        ];
    }

    /**
     * Authenticate a user and issue tokens
     */
    public function login(array $credentials, string $ipAddress = ''): array
    {
        $user = $this->userModel->findByEmail($credentials['email']);

        // Use constant-time comparison to prevent timing attacks
        if (!$user || !password_verify($credentials['password'], $user['password_hash'])) {
            throw new \RuntimeException('Invalid email or password.', 401);
        }

        // Check if email is verified
        if (!$user['email_verified']) {
            throw new \RuntimeException('Please verify your email address before logging in.', 403);
        }

        // Check if account is soft-deleted
        if (!empty($user['deleted_at'])) {
            throw new \RuntimeException('This account has been deactivated.', 403);
        }

        // Generate tokens
        $accessToken  = $this->jwtService->generateAccessToken([
            'sub'   => $user['id'],
            'email' => $user['email'],
            'role'  => $user['role'],
            'plan'  => $user['plan'],
        ]);

        $refreshToken = $this->jwtService->generateRefreshToken($user['id']);

        // Store refresh token (hashed)
        $this->refreshTokenModel->store($user['id'], $refreshToken, $ipAddress);

        // Rehash password if bcrypt cost factor changed
        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $newHash = password_hash($credentials['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $this->userModel->updatePassword($user['id'], $newHash);
        }

        return [
            'user'          => $this->sanitizeUser($user),
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => (int) ($_ENV['JWT_ACCESS_EXPIRY'] ?? 900),
        ];
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshToken(string $refreshToken, string $ipAddress = ''): array
    {
        // Validate JWT structure first
        try {
            $payload = $this->jwtService->validateToken($refreshToken);
        } catch (\RuntimeException $e) {
            throw new \RuntimeException('Invalid refresh token.', 401);
        }

        if (($payload->type ?? '') !== 'refresh') {
            throw new \RuntimeException('Invalid token type.', 401);
        }

        // Check DB for token (not revoked, not expired)
        $tokenRecord = $this->refreshTokenModel->find($refreshToken);
        if (!$tokenRecord) {
            throw new \RuntimeException('Refresh token is invalid or has been revoked.', 401);
        }

        // Revoke old token (rotation)
        $this->refreshTokenModel->revoke($refreshToken);

        // Generate new token pair
        $user = $this->userModel->findById($tokenRecord['user_id']);

        $newAccessToken  = $this->jwtService->generateAccessToken([
            'sub'   => $user['id'],
            'email' => $user['email'],
            'role'  => $user['role'],
            'plan'  => $user['plan'],
        ]);

        $newRefreshToken = $this->jwtService->generateRefreshToken($user['id']);
        $this->refreshTokenModel->store($user['id'], $newRefreshToken, $ipAddress);

        return [
            'access_token'  => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => (int) ($_ENV['JWT_ACCESS_EXPIRY'] ?? 900),
        ];
    }

    /**
     * Logout — revoke refresh token
     */
    public function logout(string $refreshToken): bool
    {
        return $this->refreshTokenModel->revoke($refreshToken);
    }

    /**
     * Logout all devices
     */
    public function logoutAll(int $userId): bool
    {
        return $this->refreshTokenModel->revokeAllForUser($userId);
    }

    /**
     * Verify email token
     */
    public function verifyEmail(string $token): array
    {
        $record = $this->emailVerificationModel->findByToken($token);

        if (!$record) {
            throw new \RuntimeException('Invalid or expired verification token.', 400);
        }

        // Mark token as used
        $this->emailVerificationModel->markUsed($record['id']);

        // Mark user as verified
        $this->userModel->markEmailVerified($record['user_id']);

        // Send welcome email
        $user = $this->userModel->findById($record['user_id']);
        try {
            $this->emailService->sendWelcomeEmail($user['email'], $user['name']);
        } catch (\Exception $e) {
            error_log('[LaunchStack Auth] Welcome email failed: ' . $e->getMessage());
        }

        return [
            'message' => 'Email verified successfully! You can now log in.',
            'user'    => $this->sanitizeUser($user),
        ];
    }

    /**
     * Remove sensitive fields from user data before returning
     */
    private function sanitizeUser(array $user): array
    {
        unset($user['password_hash'], $user['deleted_at']);
        return $user;
    }
}
