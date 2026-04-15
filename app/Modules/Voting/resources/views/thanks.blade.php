@php($locale = app()->getLocale())
@php($dir = $locale === 'ar' ? 'rtl' : 'ltr')
<!doctype html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Thank you') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center">
<div class="bg-white rounded-2xl shadow p-10 text-center max-w-md">
    <div class="text-emerald-600 text-5xl mb-4">&#10003;</div>
    <h1 class="text-2xl font-bold text-slate-800">{{ __('Thank you for voting!') }}</h1>
    <p class="mt-2 text-slate-600">{{ $campaign->localized('title') }}</p>
    <p class="mt-4 text-sm text-slate-500">
        {{ __('Results will be announced once approved by the committee.') }}
    </p>
</div>
</body>
</html>
