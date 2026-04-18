<?php

declare(strict_types=1);

namespace App\Modules\Auth\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class PasswordResetMail extends Mailable
{
    use Queueable;

    public function __construct(
        public readonly User $user,
        public readonly string $token,
        public readonly string $mailLocale = 'en',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailLocale === 'ar'
                ? 'إعادة تعيين كلمة المرور'
                : 'Reset Your Password',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.password-reset',
            with: [
                'user'             => $this->user,
                'locale'           => $this->normalizedLocale(),
                'resetUrl'         => $this->resetUrl(),
                'expiresInMinutes' => (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
            ],
        );
    }

    private function resetUrl(): string
    {
        return route('password.reset', [
            'token'  => $this->token,
            'email'  => $this->user->getEmailForPasswordReset(),
            'locale' => $this->normalizedLocale(),
        ]);
    }

    private function normalizedLocale(): string
    {
        return $this->mailLocale === 'ar' ? 'ar' : 'en';
    }
}
