<?php

declare(strict_types=1);

/**
 * LaunchStack — Refresh Token Model
 * Secure refresh token storage with rotation support
 */

namespace LaunchStack\Models;

use LaunchStack\Config\Database;

class RefreshToken
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Store a new refresh token
     */
    public function store(int $userId, string $token, string $ipAddress = ''): bool
    {
        $expiresAt = date('Y-m-d H:i:s', time() + (int) ($_ENV['JWT_REFRESH_EXPIRY'] ?? 2592000));

        $stmt = $this->db->query(
            "INSERT INTO refresh_tokens (user_id, token_hash, ip_address, expires_at, created_at)
             VALUES (:user_id, :token_hash, :ip_address, :expires_at, NOW())",
            [
                ':user_id'    => $userId,
                ':token_hash' => hash('sha256', $token), // Never store raw token
                ':ip_address' => $ipAddress,
                ':expires_at' => $expiresAt,
            ]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Find and validate a refresh token
     */
    public function find(string $token): ?array
    {
        $hash = hash('sha256', $token);
        $stmt = $this->db->query(
            "SELECT rt.*, u.email, u.name, u.role, u.plan, u.email_verified
             FROM refresh_tokens rt
             JOIN users u ON rt.user_id = u.id
             WHERE rt.token_hash = :hash 
               AND rt.revoked = 0 
               AND rt.expires_at > NOW()
             LIMIT 1",
            [':hash' => $hash]
        );

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Revoke a specific token (logout)
     */
    public function revoke(string $token): bool
    {
        $hash = hash('sha256', $token);
        $stmt = $this->db->query(
            "UPDATE refresh_tokens SET revoked = 1, revoked_at = NOW() WHERE token_hash = :hash",
            [':hash' => $hash]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Revoke all tokens for a user (security: logout all devices)
     */
    public function revokeAllForUser(int $userId): bool
    {
        $stmt = $this->db->query(
            "UPDATE refresh_tokens SET revoked = 1, revoked_at = NOW() WHERE user_id = :user_id AND revoked = 0",
            [':user_id' => $userId]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete expired tokens (cleanup job)
     */
    public function deleteExpired(): int
    {
        $stmt = $this->db->query(
            "DELETE FROM refresh_tokens WHERE expires_at < NOW() OR revoked = 1"
        );
        return $stmt->rowCount();
    }

    /**
     * Count active sessions for a user
     */
    public function countActiveSessions(int $userId): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) as count FROM refresh_tokens 
             WHERE user_id = :user_id AND revoked = 0 AND expires_at > NOW()",
            [':user_id' => $userId]
        );
        return (int) ($stmt->fetch()['count'] ?? 0);
    }
}
