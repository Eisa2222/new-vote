@php($locale = app()->getLocale())
@php($dir = $locale === 'ar' ? 'rtl' : 'ltr')
<!doctype html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Sign in') }} — SFPA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        body { font-family: {{ $locale === 'ar' ? "'Tajawal','Cairo',sans-serif" : "'Inter',system-ui,sans-serif" }}; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-600 text-white text-3xl font-bold mb-4">
            S
        </div>
        <h1 class="text-2xl font-bold text-white">{{ __('SFPA Voting Admin') }}</h1>
        <p class="text-slate-300 text-sm mt-1">{{ __('Saudi Football Players Association') }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow-2xl p-8">
        <h2 class="text-xl font-semibold text-slate-800 mb-6">{{ __('Sign in to your account') }}</h2>

        @if ($errors->any())
            <div class="mb-4 bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-3 rounded text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('status'))
            <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-3 rounded text-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="post" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                       placeholder="admin@sfpa.sa">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Password') }}</label>
                <input type="password" name="password" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                       placeholder="••••••••">
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                {{ __('Remember me') }}
            </label>

            <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg transition">
                {{ __('Sign in') }}
            </button>
        </form>
    </div>

    <div class="text-center mt-6 text-sm text-slate-400">
        <a href="/set-locale/ar" class="hover:text-white underline">العربية</a>
        <span class="mx-2">·</span>
        <a href="/set-locale/en" class="hover:text-white underline">English</a>
    </div>
</div>

</body>
</html>
