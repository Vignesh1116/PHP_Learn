<?php

declare(strict_types=1);

/**
 * LaunchStack — User Model
 * Handles all user database operations with prepared statements
 */

namespace LaunchStack\Models;

use LaunchStack\Config\Database;

class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new user
     */
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO users (name, email, password_hash, role, plan, email_verified, created_at, updated_at)
            VALUES (:name, :email, :password_hash, :role, :plan, 0, NOW(), NOW())
        ";

        $this->db->query($sql, [
            ':name'          => $data['name'],
            ':email'         => $data['email'],
            ':password_hash' => $data['password_hash'],
            ':role'          => $data['role']  ?? 'user',
            ':plan'          => $data['plan']  ?? 'free',
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->query(
            "SELECT * FROM users WHERE email = :email LIMIT 1",
            [':email' => $email]
        );
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->query(
            "SELECT id, name, email, role, plan, email_verified, created_at, updated_at FROM users WHERE id = :id LIMIT 1",
            [':id' => $id]
        );
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Update email_verified flag
     */
    public function markEmailVerified(int $userId): bool
    {
        $stmt = $this->db->query(
            "UPDATE users SET email_verified = 1, updated_at = NOW() WHERE id = :id",
            [':id' => $userId]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Update user password
     */
    public function updatePassword(int $userId, string $passwordHash): bool
    {
        $stmt = $this->db->query(
            "UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :id",
            [':hash' => $passwordHash, ':id' => $userId]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Update user profile
     */
    public function updateProfile(int $userId, array $data): bool
    {
        $stmt = $this->db->query(
            "UPDATE users SET name = :name, updated_at = NOW() WHERE id = :id",
            [':name' => $data['name'], ':id' => $userId]
        );
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if email already exists
     */
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) as count FROM users WHERE email = :email",
            [':email' => $email]
        );
        $result = $stmt->fetch();
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Get all users (admin only)
     */
    public function getAllPaginated(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $users = $this->db->query(
            "SELECT id, name, email, role, plan, email_verified, created_at FROM users 
             ORDER BY created_at DESC LIMIT :limit OFFSET :offset",
            [':limit' => $perPage, ':offset' => $offset]
        )->fetchAll();

        $total = $this->db->query(
            "SELECT COUNT(*) as count FROM users"
        )->fetch()['count'] ?? 0;

        return ['users' => $users, 'total' => (int) $total];
    }

    /**
     * Soft delete a user
     */
    public function softDelete(int $userId): bool
    {
        $stmt = $this->db->query(
            "UPDATE users SET deleted_at = NOW() WHERE id = :id",
            [':id' => $userId]
        );
        return $stmt->rowCount() > 0;
    }
}
