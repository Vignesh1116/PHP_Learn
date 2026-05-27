<?php

declare(strict_types=1);

/**
 * LaunchStack — Email Service
 * Handles transactional emails via PHPMailer + SMTP
 */

namespace LaunchStack\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailerException;

class EmailService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    private function configure(): void
    {
        $this->mailer->isSMTP();
        $this->mailer->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io';
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $_ENV['MAIL_USERNAME'] ?? '';
        $this->mailer->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->mailer->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port       = (int) ($_ENV['MAIL_PORT'] ?? 587);
        $this->mailer->CharSet    = PHPMailer::CHARSET_UTF8;
        $this->mailer->isHTML(true);

        // Only enable debug in development
        if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
            $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
        }
    }

    /**
     * Send email verification link
     */
    public function sendVerificationEmail(string $toEmail, string $toName, string $token): bool
    {
        $verifyUrl = ($_ENV['APP_URL'] ?? 'http://localhost:8000')
            . '/api/auth/verify-email?token=' . urlencode($token);

        $html = $this->getVerificationEmailTemplate($toName, $verifyUrl);

        return $this->send($toEmail, $toName, '✅ Verify Your LaunchStack Account', $html);
    }

    /**
     * Send welcome email after verification
     */
    public function sendWelcomeEmail(string $toEmail, string $toName): bool
    {
        $html = $this->getWelcomeEmailTemplate($toName);
        return $this->send($toEmail, $toName, '🚀 Welcome to LaunchStack!', $html);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(string $toEmail, string $toName, string $token): bool
    {
        $resetUrl = ($_ENV['FRONTEND_URL'] ?? 'http://localhost:5173')
            . '/reset-password?token=' . urlencode($token);

        $html = $this->getPasswordResetTemplate($toName, $resetUrl);
        return $this->send($toEmail, $toName, '🔒 Reset Your LaunchStack Password', $html);
    }

    /**
     * Core send method
     */
    private function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody
    ): bool {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->mailer->setFrom(
                $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@launchstack.io',
                $_ENV['MAIL_FROM_NAME']    ?? 'LaunchStack'
            );

            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $htmlBody;
            $this->mailer->AltBody = strip_tags($htmlBody);

            $this->mailer->send();
            return true;
        } catch (MailerException $e) {
            error_log('[LaunchStack Email] Failed to send to ' . $toEmail . ': ' . $e->getMessage());
            return false;
        }
    }

    private function getVerificationEmailTemplate(string $name, string $verifyUrl): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="font-family: Inter, Arial, sans-serif; background:#f8f9fa; padding:40px;">
            <div style="max-width:580px; margin:0 auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.08);">
                <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6); padding:40px; text-align:center;">
                    <h1 style="color:#fff; margin:0; font-size:28px;">🚀 LaunchStack</h1>
                    <p style="color:rgba(255,255,255,0.85); margin:8px 0 0;">SaaS Billing & Subscription Platform</p>
                </div>
                <div style="padding:40px;">
                    <h2 style="color:#1e293b; font-size:22px;">Hi {$name}, verify your email</h2>
                    <p style="color:#64748b; line-height:1.7;">Thanks for signing up! Click the button below to verify your email address and activate your account.</p>
                    <div style="text-align:center; margin:32px 0;">
                        <a href="{$verifyUrl}" style="background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; text-decoration:none; padding:14px 36px; border-radius:8px; font-weight:600; font-size:16px; display:inline-block;">Verify Email Address</a>
                    </div>
                    <p style="color:#94a3b8; font-size:13px;">This link expires in 24 hours. If you didn't create an account, you can safely ignore this email.</p>
                    <hr style="border:none; border-top:1px solid #f1f5f9; margin:24px 0;">
                    <p style="color:#94a3b8; font-size:12px; text-align:center;">© 2024 LaunchStack. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    private function getWelcomeEmailTemplate(string $name): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="font-family: Inter, Arial, sans-serif; background:#f8f9fa; padding:40px;">
            <div style="max-width:580px; margin:0 auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.08);">
                <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6); padding:40px; text-align:center;">
                    <h1 style="color:#fff; margin:0; font-size:28px;">🎉 Welcome to LaunchStack!</h1>
                </div>
                <div style="padding:40px;">
                    <h2 style="color:#1e293b;">You're all set, {$name}!</h2>
                    <p style="color:#64748b; line-height:1.7;">Your account has been verified. You now have access to:</p>
                    <ul style="color:#64748b; line-height:2;">
                        <li>✅ Free plan with 1,000 API requests/month</li>
                        <li>🔑 API Key management</li>
                        <li>📊 Usage analytics dashboard</li>
                        <li>💳 Upgrade to Pro anytime</li>
                    </ul>
                    <hr style="border:none; border-top:1px solid #f1f5f9; margin:24px 0;">
                    <p style="color:#94a3b8; font-size:12px; text-align:center;">© 2024 LaunchStack. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    private function getPasswordResetTemplate(string $name, string $resetUrl): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="font-family: Inter, Arial, sans-serif; background:#f8f9fa; padding:40px;">
            <div style="max-width:580px; margin:0 auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.08);">
                <div style="background:linear-gradient(135deg,#ef4444,#f97316); padding:40px; text-align:center;">
                    <h1 style="color:#fff; margin:0; font-size:28px;">🔒 Password Reset</h1>
                </div>
                <div style="padding:40px;">
                    <h2 style="color:#1e293b;">Hi {$name},</h2>
                    <p style="color:#64748b; line-height:1.7;">We received a request to reset your LaunchStack password. Click the button below to set a new password.</p>
                    <div style="text-align:center; margin:32px 0;">
                        <a href="{$resetUrl}" style="background:linear-gradient(135deg,#ef4444,#f97316); color:#fff; text-decoration:none; padding:14px 36px; border-radius:8px; font-weight:600; font-size:16px; display:inline-block;">Reset Password</a>
                    </div>
                    <p style="color:#94a3b8; font-size:13px;">This link expires in 1 hour. If you didn't request a password reset, you can safely ignore this email.</p>
                    <hr style="border:none; border-top:1px solid #f1f5f9; margin:24px 0;">
                    <p style="color:#94a3b8; font-size:12px; text-align:center;">© 2024 LaunchStack. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}
