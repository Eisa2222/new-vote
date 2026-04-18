<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

final class ResetPasswordController extends Controller
{
    public function show(string $token): View
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            fn ($user, $password) => $user->forceFill([
                'password' => Hash::make($password),
            ])->save()
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __('Password reset successfully. Please sign in.'))
            : back()->withErrors(['email' => __($status)]);
    }
}
