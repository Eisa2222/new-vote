@php($locale = app()->getLocale())
@php($dir = $locale === 'ar' ? 'rtl' : 'ltr')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Reset Password') }} — FPA</title>
    @include('partials.brand-head')
</head>
<body class="bg-ink-50">
<div class="min-h-screen bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-8 md:p-12">

        <h2 class="text-2xl font-bold text-ink-900">{{ __('Reset Password') }}</h2>
        <p class="text-ink-500 mt-2 text-sm mb-6">{{ __('Enter your new password below.') }}</p>

        @if($errors->any())
            <div class="mb-4 rounded-2xl bg-danger-500/5 border border-danger-500/30 text-danger-600 p-3 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="post" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label class="block text-sm font-medium mb-1.5 text-ink-800">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-xl border border-ink-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                       placeholder="email@example.com">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5 text-ink-800">{{ __('New Password') }}</label>
                <input type="password" name="password" required
                       class="w-full rounded-xl border border-ink-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                       placeholder="••••••••">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5 text-ink-800">{{ __('Confirm Password') }}</label>
                <input type="password" name="password_confirmation" required
                       class="w-full rounded-xl border border-ink-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                       placeholder="••••••••">
            </div>
            <button class="w-full rounded-xl bg-brand-600 hover:bg-brand-700 text-white py-3.5 font-semibold transition">
                {{ __('Reset Password') }}
            </button>
        </form>
    </div>
</div>
</body>
</html>
