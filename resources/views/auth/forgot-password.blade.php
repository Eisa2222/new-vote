@php($locale = app()->getLocale())
@php($dir = $locale === 'ar' ? 'rtl' : 'ltr')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Forgot Password') }} — FPA</title>
    @include('partials.brand-head')
</head>
<body class="bg-ink-50">
<div class="min-h-screen bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-8 md:p-12">

        <h2 class="text-2xl font-bold text-ink-900">{{ __('Forgot Password') }}</h2>
        <p class="text-ink-500 mt-2 text-sm mb-6">{{ __('Enter your email and we\'ll send you a reset link.') }}</p>

        @if(session('status'))
            <div class="mb-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 p-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-2xl bg-danger-500/5 border border-danger-500/30 text-danger-600 p-3 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="post" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1.5 text-ink-800">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded-xl border border-ink-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                       placeholder="email@example.com">
            </div>
            <button class="w-full rounded-xl bg-brand-600 hover:bg-brand-700 text-white py-3.5 font-semibold transition">
                {{ __('Send Reset Link') }}
            </button>
        </form>

        <a href="{{ route('login') }}" class="block text-center mt-5 text-sm text-ink-500 hover:text-brand-600">
            ← {{ __('Back to Sign in') }}
        </a>
    </div>
</div>
</body>
</html>
