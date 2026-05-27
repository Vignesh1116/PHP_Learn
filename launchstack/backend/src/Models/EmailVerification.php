<?php

declare(strict_types=1);

/**
 * LaunchStack — Email Verification Model
 * Handles secure email verification token storage
 */

namespace LaunchStack\Models;

use LaunchStack\Config\Database;

class EmailVerification
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new verification token
     */
    public function create(int $userId, string $email): string
    {
        // Delete any existing tokens for this user
        $this->deleteForUser($userId);

        $token     = bin2hex(random_bytes(32)); // 64-char hex token
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->db->query(
            "INSERT INTO email_verifications (user_id, email, token_hash, expires_at, created_at)
             VALUES (:user_id, :email, :token_hash, :expires_at, NOW())",
            [
                ':user_id'    => $userId,
                ':email'      => $email,
                ':token_hash' => hash('sha256', $token),
                ':expires_at' => $expiresAt,
            ]
        );

        return $token; // Return raw token for email
    }

    /**
     * Validate a verification token
     */
    public function findByToken(string $token): ?array
    {
        $hash = hash('sha256', $token);
        $stmt = $this->db->query(
            "SELECT * FROM email_verifications 
             WHERE token_hash = :hash AND expires_at > NOW() AND used = 0
             LIMIT 1",
            [':hash' => $hash]
        );
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Mark token as used
     */
    public function markUsed(int $id): bool
    {
        $stmt = $this->db->query(
            "UPDATE email_verifications SET used = 1, used_at = NOW() WHERE id = :id",
            [':id' => $id]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete all tokens for a user
     */
    public function deleteForUser(int $userId): void
    {
        $this->db->query(
            "DELETE FROM email_verifications WHERE user_id = :user_id",
            [':user_id' => $userId]
        );
    }
}
