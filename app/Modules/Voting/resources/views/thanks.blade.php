@php($locale = app()->getLocale())
@php($dir = $locale === 'ar' ? 'rtl' : 'ltr')
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Thank you') }}</title>
    @include('partials.brand-head')
</head>
<body class="bg-gradient-to-br from-brand-50 via-white to-brand-100 min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-3xl shadow-brand p-12 text-center max-w-lg border border-ink-200">
    <div class="w-20 h-20 mx-auto rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-4xl mb-6">&#10003;</div>
    <h1 class="text-3xl font-bold text-ink-900">{{ __('Thank you for voting!') }}</h1>
    <p class="mt-3 text-ink-700 text-lg">{{ $campaign->localized('title') }}</p>
    <p class="mt-5 text-sm text-ink-500 leading-7">
        {{ __('Results will be announced once approved by the committee.') }}
    </p>
    <div class="mt-8 pt-6 border-t border-ink-200 text-xs text-ink-500">
        © {{ date('Y') }} {{ __('Saudi Football Players Association') }}
    </div>
</div>
</body>
</html>
