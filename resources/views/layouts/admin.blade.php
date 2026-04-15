@php($locale = app()->getLocale())
@php($dir = $locale === 'ar' ? 'rtl' : 'ltr')
<!doctype html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        body { font-family: {{ $locale === 'ar' ? "'Tajawal','Cairo',sans-serif" : "'Inter',system-ui,sans-serif" }}; }
        .btn-primary  { @apply bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2 rounded-lg; }
        .btn-ghost    { @apply text-slate-600 hover:bg-slate-100 px-3 py-2 rounded-lg; }
        .badge-active   { @apply bg-emerald-100 text-emerald-700 px-2 py-0.5 text-xs rounded-full; }
        .badge-inactive { @apply bg-slate-200 text-slate-600 px-2 py-0.5 text-xs rounded-full; }
        .badge-draft     { @apply bg-slate-100 text-slate-700 px-2 py-0.5 text-xs rounded-full; }
        .badge-published { @apply bg-blue-100 text-blue-700 px-2 py-0.5 text-xs rounded-full; }
        .badge-closed    { @apply bg-rose-100 text-rose-700 px-2 py-0.5 text-xs rounded-full; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex">
    <aside class="w-64 bg-slate-900 text-slate-100 min-h-screen p-4">
        <div class="text-xl font-bold mb-6">SFPA {{ __('Admin') }}</div>
        <nav class="space-y-1">
            <a href="/admin"           class="block px-3 py-2 rounded hover:bg-slate-800">{{ __('Dashboard') }}</a>
            <a href="/admin/clubs"     class="block px-3 py-2 rounded hover:bg-slate-800">{{ __('Clubs') }}</a>
            <a href="/admin/players"   class="block px-3 py-2 rounded hover:bg-slate-800">{{ __('Players') }}</a>
            <a href="/admin/campaigns" class="block px-3 py-2 rounded hover:bg-slate-800">{{ __('Campaigns') }}</a>
            <a href="/admin/users"     class="block px-3 py-2 rounded hover:bg-slate-800">{{ __('Users') }}</a>
        </nav>
        <div class="mt-8 pt-6 border-t border-slate-700 text-sm">
            @auth
                <div class="text-slate-300 mb-2">{{ auth()->user()->name }}</div>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-rose-400 hover:text-rose-300">{{ __('Sign out') }}</button>
                </form>
            @endauth
            <div class="mt-3 text-slate-400">
                <a href="/set-locale/ar" class="underline">العربية</a> /
                <a href="/set-locale/en" class="underline">English</a>
            </div>
        </div>
    </aside>
    <main class="flex-1 p-8">
        @if(session('success'))
            <div class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 p-3 rounded">
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
