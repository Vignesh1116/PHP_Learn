<div align="center">

<img src="https://readme-typing-svg.demolab.com?font=Fira+Code&weight=700&size=42&pause=800&color=6366F1&center=true&vCenter=true&width=600&height=80&lines=LaunchStack+🚀;SaaS+Billing+Platform;Built+for+Scale" alt="LaunchStack" />

<br/>

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![React](https://img.shields.io/badge/React-18+-61DAFB?style=for-the-badge&logo=react&logoColor=black)](https://reactjs.org)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Redis](https://img.shields.io/badge/Redis-7.0+-DC382D?style=for-the-badge&logo=redis&logoColor=white)](https://redis.io)
[![Stripe](https://img.shields.io/badge/Stripe-Payments-635BFF?style=for-the-badge&logo=stripe&logoColor=white)](https://stripe.com)
[![JWT](https://img.shields.io/badge/JWT-Auth-000000?style=for-the-badge&logo=jsonwebtokens&logoColor=white)](https://jwt.io)

<br/>

> **LaunchStack** is a production-grade SaaS Billing & Subscription Platform — the same infrastructure that powers real startups. Built from scratch using PHP, React, MySQL, Redis, and Stripe.

<br/>

---

</div>

## 🌟 Why LaunchStack Stands Out

Most developers build todo apps and blogs. **LaunchStack is what companies actually pay for:**

| What You Built | What Companies Pay For |
|---|---|
| ✅ CRUD apps | 💳 Payment systems |
| ✅ Blog apps | 🔐 JWT authentication |
| ✅ Todo lists | 📊 Usage analytics |
| ✅ REST APIs | 🔔 Webhook processing |
| ✅ Forms | 💰 Subscription billing |

This project demonstrates **real startup engineering**. The same systems used at Stripe, Shopify, and Razorpay.

---

## 🏗️ System Architecture

```
╔══════════════════════════════════════════════════════════════════╗
║                         CLIENT LAYER                            ║
║                   React 18 SPA (Vite + Axios)                  ║
║          Authentication │ Dashboard │ Billing Portal           ║
╚═══════════════════════════════╦══════════════════════════════════╝
                                │  HTTPS / REST API
                                ▼
╔══════════════════════════════════════════════════════════════════╗
║                        API GATEWAY                              ║
║                   PHP 8.2 (Custom Router)                       ║
║   ┌──────────────┬──────────────┬──────────────────────────┐   ║
║   │ JWT Middleware│Rate Limiter │  Request Validator        │   ║
║   └──────────────┴──────────────┴──────────────────────────┘   ║
╚════════╦══════════════════╦══════════════════╦═══════════════════╝
         │                  │                  │
         ▼                  ▼                  ▼
╔════════════════╗ ╔════════════════╗ ╔════════════════╗
║   MySQL 8.0    ║ ║   Redis 7.0    ║ ║   Stripe API   ║
║                ║ ║                ║ ║                ║
║  • Users       ║ ║  • Sessions    ║ ║  • Checkout    ║
║  • Plans       ║ ║  • Rate limits ║ ║  • Webhooks    ║
║  • Invoices    ║ ║  • API cache   ║ ║  • Portal      ║
║  • API Keys    ║ ║  • Job queue   ║ ║  • Refunds     ║
╚════════════════╝ ╚════════════════╝ ╚════════════════╝
```

---

## 📦 Feature Breakdown

### 🔐 Phase 1 — Authentication System ✅
> **JWT-powered auth with military-grade security**

- **Access tokens** (15-minute expiry) + **Refresh tokens** (30-day rotation)
- **bcrypt** password hashing with adaptive cost factor (12 rounds)
- Email verification with **secure one-time tokens** (SHA-256 hashed in DB)
- **Timing attack prevention** — constant-time password comparison
- **Token rotation** — old refresh token revoked on every use
- Multi-device session tracking & logout-all-devices

**Endpoints:**
```http
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/refresh
POST   /api/auth/logout
POST   /api/auth/logout-all
GET    /api/auth/verify-email?token=
POST   /api/auth/resend-verification
GET    /api/user/profile
PATCH  /api/user/profile
POST   /api/user/change-password
```

---

### 💳 Phase 2 — Subscription Plans *(Coming Soon)*
> **Free / Pro / Enterprise plan management**

- Plan feature gates via JWT payload
- Upgrade/downgrade flows
- Plan comparison API

---

### 💰 Phase 3 — Stripe Integration *(Coming Soon)*
> **Full payment infrastructure**

- Stripe Checkout Sessions
- Customer Portal (self-serve billing)
- Invoice generation
- Payment method management

---

### 🔔 Phase 4 — Payment Webhooks *(Coming Soon)*
> **Event-driven subscription updates**

```
Stripe Event → PHP Webhook Handler → DB Update → Email Notification
```

- Signature verification (HMAC-SHA256)
- Idempotent event processing
- Failed payment retry logic

---

### 🔑 Phase 5 — API Key Management *(Coming Soon)*
> **Developer-grade API keys**

- Generate/revoke/rotate API keys
- Key scoping (read-only / read-write / admin)
- Last-used tracking

---

### 📊 Phase 6 — Usage Metering *(Coming Soon)*
> **Real-time API usage analytics powered by Redis**

- Per-user request counting
- Rate limiting (sliding window algorithm)
- Plan-based quota enforcement

---

### 📧 Phase 7 — Email Queue System *(Coming Soon)*
> **Async background jobs for emails and notifications**

- Redis-backed job queue
- Worker process architecture
- Retry with exponential backoff

---

### 🛡️ Phase 8 — Security Layer *(Coming Soon)*
> **Enterprise-grade security hardening**

- CSRF token protection
- SQL injection prevention (PDO prepared statements)
- Input sanitization & XSS prevention
- Security headers (CSP, HSTS, X-Frame-Options)

---

### 🖥️ Phase 9 — Admin Dashboard *(Coming Soon)*
> **Business intelligence at a glance**

- Monthly Recurring Revenue (MRR) charts
- Active subscribers by plan
- Churn rate tracking
- User management

---

### ⚛️ Phase 10 — React Frontend *(Coming Soon)*
> **Production-ready SaaS UI**

- Login/Register pages with JWT
- Subscription management
- API key dashboard
- Usage analytics charts (Recharts)
- Stripe billing portal integration

---

## 🚀 Quick Start

### Prerequisites

| Requirement | Version |
|-------------|---------|
| PHP | 8.1+ |
| Composer | 2.0+ |
| MySQL | 8.0+ |
| Redis | 7.0+ |
| Node.js | 18+ |

### 1. Clone the Repository

```bash
git clone https://github.com/Vignesh1116/PHP_Learn.git
cd PHP_Learn/launchstack
```

### 2. Install Dependencies

```bash
cd backend
composer install
```

### 3. Configure Environment

```bash
cp .env.example .env
# Edit .env with your database credentials, JWT secret, SMTP settings
```

### 4. Setup Database

```bash
# Create database and run migrations
mysql -u root -p < database/migrations/001_create_users_table.sql
```

### 5. Start Development Server

```bash
composer serve
# Server running at http://localhost:8000
```

### 6. Test the API

```bash
# Health check
curl http://localhost:8000/api/health

# Register a user
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!"
  }'

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "john@example.com", "password": "SecurePass123!"}'

# Access protected route
curl http://localhost:8000/api/user/profile \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

---

## 🗄️ Database Schema

```sql
users
├── id              BIGINT UNSIGNED PK
├── name            VARCHAR(100)
├── email           VARCHAR(255) UNIQUE
├── password_hash   VARCHAR(255)     -- bcrypt cost=12
├── role            ENUM(user,admin)
├── plan            ENUM(free,pro,enterprise)
├── email_verified  TINYINT(1)
└── timestamps

refresh_tokens
├── id              BIGINT UNSIGNED PK
├── user_id         FK → users.id
├── token_hash      VARCHAR(64)      -- SHA-256 hash, never raw
├── ip_address      VARCHAR(45)
├── revoked         TINYINT(1)
└── expires_at

email_verifications
├── id              BIGINT UNSIGNED PK
├── user_id         FK → users.id
├── token_hash      VARCHAR(64)      -- SHA-256 hash, one-time use
├── used            TINYINT(1)
└── expires_at
```

---

## 🔒 Security Features

| Feature | Implementation |
|---------|---------------|
| Password Hashing | bcrypt (cost=12, adaptive) |
| SQL Injection | PDO prepared statements everywhere |
| Token Storage | SHA-256 hashed (never raw tokens in DB) |
| XSS Prevention | Input sanitization + security headers |
| Timing Attacks | `password_verify()` constant-time |
| Token Rotation | Old refresh token revoked on use |
| CORS | Whitelist-based origin validation |

---

## 🧑‍💻 Advanced Concepts Demonstrated

### 1. JWT Token Architecture
```
Access Token (15 min)  ──► Short-lived, stateless API access
Refresh Token (30 days) ──► Long-lived, stored hashed in DB
Token Rotation          ──► New pair issued on every refresh
```

### 2. Password Security Pipeline
```
Raw Password → password_hash(bcrypt, cost=12) → Stored Hash
Login → password_verify() → Constant-time → Auth Decision
Future logins → password_needs_rehash() → Auto-upgrade cost
```

### 3. Webhook Processing (Phase 4)
```
Stripe Event → Signature Verify → Idempotency Check → Process → Respond 200ms
```

### 4. Redis Rate Limiting (Phase 6)
```
Request → INCR key → Check count → Allow/Block → EXPIRE window
Algorithm: Sliding Window Counter
```

---

## 📁 Project Structure

```
launchstack/
├── backend/
│   ├── config/
│   │   ├── App.php           # Bootstrap & security headers
│   │   ├── Database.php      # Singleton PDO connection
│   │   └── JwtConfig.php     # JWT settings
│   ├── src/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   └── UserController.php
│   │   ├── Middleware/
│   │   │   ├── JwtMiddleware.php
│   │   │   └── CorsMiddleware.php
│   │   ├── Models/
│   │   │   ├── User.php
│   │   │   ├── RefreshToken.php
│   │   │   └── EmailVerification.php
│   │   ├── Services/
│   │   │   ├── AuthService.php
│   │   │   ├── JwtService.php
│   │   │   └── EmailService.php
│   │   ├── Helpers/
│   │   │   ├── Response.php
│   │   │   └── Validator.php
│   │   └── Router.php
│   ├── database/migrations/
│   │   └── 001_create_users_table.sql
│   ├── public/index.php      # Entry point
│   ├── composer.json
│   └── .env.example
└── README.md
```

---

## 📈 What This Proves to Employers

```
✅ You understand payment systems — not just CRUD
✅ You implement security properly (JWT, bcrypt, SQL injection)
✅ You know distributed systems (Redis caching, queues)
✅ You handle async events (webhooks, background jobs)
✅ You architect for scale (connection pooling, indexing)
✅ You write production-quality code (error handling, logging)
```

---

## 🛣️ Roadmap

- [x] Phase 1: Authentication System (JWT + bcrypt + Email Verification)
- [ ] Phase 2: Subscription Plan Management
- [ ] Phase 3: Stripe Payment Integration
- [ ] Phase 4: Payment Webhooks
- [ ] Phase 5: API Key System
- [ ] Phase 6: Usage Metering & Rate Limiting
- [ ] Phase 7: Email Queue System
- [ ] Phase 8: Security Hardening
- [ ] Phase 9: Admin Dashboard
- [ ] Phase 10: React Frontend

---

## 🧰 Tech Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Backend** | PHP 8.2 | API server |
| **Frontend** | React 18 + Vite | SPA UI |
| **Database** | MySQL 8.0 | Primary data store |
| **Cache** | Redis 7.0 | Sessions, rate limiting |
| **Payments** | Stripe | Billing & subscriptions |
| **Auth** | JWT (firebase/php-jwt) | Stateless authentication |
| **Email** | PHPMailer + SMTP | Transactional emails |
| **Env** | phpdotenv | 12-factor config |
| **Logging** | Monolog | Structured logging |

---

## 📜 License

MIT License — free to use, learn from, and build upon.

---

<div align="center">

**Built with ❤️ by Vignesh**

*This is what real SaaS engineering looks like.*

[![GitHub](https://img.shields.io/badge/GitHub-Vignesh1116-181717?style=for-the-badge&logo=github)](https://github.com/Vignesh1116)

</div>
