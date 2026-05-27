-- ============================================================
-- LaunchStack — Migration 001: Initial Authentication Schema
-- Phase 1: Users, Refresh Tokens, Email Verifications
-- ============================================================

CREATE DATABASE IF NOT EXISTS launchstack_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE launchstack_db;

-- ─────────────────────────────────────────────────────────────
-- Table: users
-- Core user accounts with subscription plan tracking
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)        NOT NULL,
    email           VARCHAR(255)        NOT NULL,
    password_hash   VARCHAR(255)        NOT NULL,
    role            ENUM('user','admin') NOT NULL DEFAULT 'user',
    plan            ENUM('free','pro','enterprise') NOT NULL DEFAULT 'free',
    email_verified  TINYINT(1)          NOT NULL DEFAULT 0,
    avatar_url      VARCHAR(500)        NULL,
    timezone        VARCHAR(50)         NOT NULL DEFAULT 'UTC',
    last_login_at   TIMESTAMP           NULL,
    created_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP           NULL,

    UNIQUE KEY uq_users_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_plan (plan),
    INDEX idx_users_created_at (created_at),
    INDEX idx_users_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- Table: refresh_tokens
-- Secure refresh token storage with revocation support
-- Note: We store SHA-256 hash of the token, never raw token
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS refresh_tokens (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,
    token_hash  VARCHAR(64)     NOT NULL,   -- SHA-256 = 64 hex chars
    ip_address  VARCHAR(45)     NOT NULL DEFAULT '', -- IPv6 max is 45 chars
    revoked     TINYINT(1)      NOT NULL DEFAULT 0,
    revoked_at  TIMESTAMP       NULL,
    expires_at  TIMESTAMP       NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_token_hash (token_hash),
    INDEX idx_rt_user_id (user_id),
    INDEX idx_rt_expires_at (expires_at),
    INDEX idx_rt_revoked (revoked),

    CONSTRAINT fk_rt_user_id
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- Table: email_verifications
-- Stores email verification tokens (hashed, one-time use)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS email_verifications (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,
    email       VARCHAR(255)    NOT NULL,
    token_hash  VARCHAR(64)     NOT NULL,   -- SHA-256 hash
    used        TINYINT(1)      NOT NULL DEFAULT 0,
    used_at     TIMESTAMP       NULL,
    expires_at  TIMESTAMP       NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_ev_user_id (user_id),
    INDEX idx_ev_token_hash (token_hash),

    CONSTRAINT fk_ev_user_id
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- Table: password_resets
-- For future: Phase 8 — Forgot Password Flow
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS password_resets (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(255)    NOT NULL,
    token_hash  VARCHAR(64)     NOT NULL,
    used        TINYINT(1)      NOT NULL DEFAULT 0,
    expires_at  TIMESTAMP       NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_pr_email (email),
    INDEX idx_pr_token_hash (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- Seed: Default Admin User
-- Password: Admin@LaunchStack2024! (change immediately!)
-- ─────────────────────────────────────────────────────────────
INSERT INTO users (name, email, password_hash, role, plan, email_verified)
VALUES (
    'LaunchStack Admin',
    'admin@launchstack.io',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    'admin',
    'enterprise',
    1
) ON DUPLICATE KEY UPDATE id = id;
