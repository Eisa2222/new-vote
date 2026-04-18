<?php

declare(strict_types=1);

namespace App\Modules\Auth\Jobs;

use App\Models\User;
use App\Modules\Auth\Mail\PasswordResetMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendPasswordResetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly User $user,
        public readonly string $token,
        public readonly string $locale = 'en',
    ) {
        $this->onQueue('mail');
    }

    public function handle(): void
    {
        Mail::to($this->user->email)->send(
            new PasswordResetMail($this->user, $this->token, $this->locale)
        );
    }
}
