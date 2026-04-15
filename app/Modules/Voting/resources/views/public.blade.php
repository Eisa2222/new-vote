@php($locale = app()->getLocale())
@php($dir = $locale === 'ar' ? 'rtl' : 'ltr')
<!doctype html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $campaign->localized('title') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        body { font-family: {{ $locale === 'ar' ? "'Tajawal', 'Cairo', sans-serif" : "'Inter', system-ui, sans-serif" }}; }
        .card { @apply bg-white rounded-2xl shadow-md p-6 mb-4; }
        .candidate { @apply border rounded-xl p-4 cursor-pointer transition; }
        .candidate:hover { @apply border-emerald-500 bg-emerald-50; }
        .candidate.selected { @apply border-emerald-600 bg-emerald-100 ring-2 ring-emerald-500; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
<div class="max-w-5xl mx-auto py-10 px-4">
    <header class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-slate-800">{{ $campaign->localized('title') }}</h1>
        @if($campaign->localized('description'))
            <p class="mt-2 text-slate-600">{{ $campaign->localized('description') }}</p>
        @endif
        <p class="mt-2 text-sm text-slate-500">
            {{ __('Voting closes at') }}: {{ $campaign->end_at->format('Y-m-d H:i') }}
        </p>
    </header>

    @if($errors->any())
        <div class="card border-l-4 border-red-500 bg-red-50">
            @foreach($errors->all() as $e)
                <p class="text-red-700">{{ $e }}</p>
            @endforeach
        </div>
    @endif

    <form method="post" action="{{ route('voting.submit', $campaign->public_token) }}" id="voteForm">
        @csrf

        @foreach($campaign->categories as $category)
            <section class="card" data-category-id="{{ $category->id }}" data-required="{{ $category->required_picks }}">
                <h2 class="text-xl font-semibold text-slate-800">{{ $category->localized('title') }}</h2>
                <p class="text-sm text-slate-500 mb-4">
                    {{ __('Pick exactly :n', ['n' => $category->required_picks]) }}
                    @if($category->position_slot !== 'any')
                        — {{ __(ucfirst($category->position_slot)) }}
                    @endif
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($category->candidates as $c)
                        @php($label = $c->player?->localized('name') ?? $c->club?->localized('name'))
                        @php($photo = $c->player?->photo_path ?? $c->club?->logo_path)
                        <label class="candidate" data-candidate-id="{{ $c->id }}">
                            <input type="checkbox"
                                   name="selections[{{ $loop->parent->index }}][candidate_ids][]"
                                   value="{{ $c->id }}"
                                   class="sr-only candidate-input">
                            <input type="hidden"
                                   name="selections[{{ $loop->parent->index }}][category_id]"
                                   value="{{ $category->id }}">
                            <div class="flex items-center gap-3">
                                @if($photo)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($photo) }}"
                                         class="w-12 h-12 rounded-full object-cover" alt="">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-slate-200"></div>
                                @endif
                                <div>
                                    <div class="font-medium text-slate-800">{{ $label }}</div>
                                    @if($c->player)
                                        <div class="text-xs text-slate-500">{{ $c->player->club?->localized('name') }}</div>
                                    @endif
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="sticky bottom-0 bg-white border-t p-4 -mx-4 mt-8">
            <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-xl">
                {{ __('Submit My Vote') }}
            </button>
        </div>
    </form>
</div>

<script>
    document.querySelectorAll('section[data-category-id]').forEach(section => {
        const required = parseInt(section.dataset.required, 10);
        section.querySelectorAll('.candidate').forEach(label => {
            const input = label.querySelector('.candidate-input');
            label.addEventListener('click', e => {
                if (e.target.tagName === 'INPUT') return;
                e.preventDefault();
                const chosen = section.querySelectorAll('.candidate-input:checked').length;
                if (!input.checked && chosen >= required) return;
                input.checked = !input.checked;
                label.classList.toggle('selected', input.checked);
            });
        });
    });

    document.getElementById('voteForm').addEventListener('submit', e => {
        const bad = [...document.querySelectorAll('section[data-category-id]')].filter(s => {
            return s.querySelectorAll('.candidate-input:checked').length !== parseInt(s.dataset.required, 10);
        });
        if (bad.length) {
            e.preventDefault();
            alert('{{ __('Please complete all categories with the required number of picks.') }}');
        }
    });
</script>
</body>
</html>
