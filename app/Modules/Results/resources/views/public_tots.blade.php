@php
    $locale = app()->getLocale();
    $dir    = $locale === 'ar' ? 'rtl' : 'ltr';

    $winnersBySlot = $result->items->where('is_winner', true)->groupBy('position');
    $formation = \App\Modules\Campaigns\Domain\TeamOfSeasonFormation::fromCampaign($campaign);
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>🏆 {{ __('Team of the Season') }} — {{ $campaign->localized('title') }}</title>
    @include('partials.brand-head')
    <style>
        .pitch-surface {
            background-color: #0B3D2E;
            background-image:
                repeating-linear-gradient(to bottom, rgba(255,255,255,0.05) 0 48px, transparent 48px 96px),
                radial-gradient(ellipse at center, rgba(255,255,255,0.08) 0%, transparent 60%),
                linear-gradient(180deg, #1F7A49 0%, #115C42 55%, #0B3D2E 100%);
        }
        .trophy-bg {
            background:
                radial-gradient(ellipse at top, rgba(200,163,101,0.25) 0%, transparent 55%),
                linear-gradient(135deg, #0B3D2E 0%, #115C42 50%, #083024 100%);
        }
    </style>
</head>
<body class="bg-ink-50 min-h-screen">

<div class="max-w-6xl mx-auto px-3 md:px-6 py-6 md:py-10 space-y-6">

    {{-- HERO --}}
    <section class="trophy-bg text-white rounded-[2rem] p-6 md:p-10 shadow-brand relative overflow-hidden text-center">
        <div class="absolute inset-0 opacity-30 pointer-events-none"
             style="background-image: radial-gradient(circle at 20% 30%, #C8A365 1.5px, transparent 2.5px),
                                       radial-gradient(circle at 80% 70%, #DDB97A 2px, transparent 3px);
                    background-size: 110px 110px, 140px 140px;"></div>

        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-accent-500/20 border border-accent-400/40 backdrop-blur text-accent-400 text-xs font-bold tracking-[0.25em] uppercase">
                🏆 {{ __('Team of the Season') }}
            </div>

            <h1 class="mt-5 text-3xl md:text-5xl font-black leading-tight">
                {{ $campaign->localized('title') }}
            </h1>
            <p class="text-brand-100 mt-3 text-sm md:text-base">
                {{ __('Official lineup approved by the Voting Committee') }}
            </p>

            <div class="mt-5 inline-flex items-center gap-4 flex-wrap justify-center text-xs text-brand-100">
                <span class="rounded-full bg-white/10 border border-white/20 px-3 py-1">
                    {{ __('Formation') }}: <strong class="text-accent-400">{{ $formation['defense'] }}-{{ $formation['midfield'] }}-{{ $formation['attack'] }}</strong>
                </span>
                <span class="rounded-full bg-white/10 border border-white/20 px-3 py-1">
                    {{ __('Total votes') }}: <strong class="text-white">{{ number_format($result->total_votes) }}</strong>
                </span>
                @if($result->announced_at)
                    <span class="rounded-full bg-white/10 border border-white/20 px-3 py-1">
                        {{ __('Announced') }}: <strong class="text-white">{{ $result->announced_at->translatedFormat('d M Y') }}</strong>
                    </span>
                @endif
            </div>
        </div>
    </section>

    {{-- PITCH with winners --}}
    <section class="rounded-3xl overflow-hidden shadow-brand border border-brand-900/30">
        <div class="pitch-surface relative p-6 md:p-12">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute inset-x-0 top-1/2 h-px bg-white/30"></div>
                <div class="absolute left-1/2 top-1/2 w-24 h-24 md:w-40 md:h-40 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white/25"></div>
                <div class="absolute inset-x-10 top-2 h-10 border-2 border-b-0 border-white/20 rounded-t-xl"></div>
                <div class="absolute inset-x-10 bottom-2 h-10 border-2 border-t-0 border-white/20 rounded-b-xl"></div>
            </div>

            <div class="relative space-y-8 md:space-y-12">
                @foreach(['attack','midfield','defense','goalkeeper'] as $slot)
                    @if(!isset($formation[$slot])) @continue @endif
                    @php $winners = $winnersBySlot->get($slot, collect())->sortBy('rank'); @endphp
                    <div>
                        <div class="text-center mb-4">
                            <div class="inline-block px-3 py-1 rounded-full bg-white/10 backdrop-blur border border-white/20 text-accent-400 text-[11px] md:text-xs font-bold tracking-[0.2em] uppercase">
                                {{ __(ucfirst($slot)) }}
                            </div>
                        </div>
                        <div class="flex flex-wrap justify-center gap-3 md:gap-6">
                            @foreach($winners as $item)
                                @php
                                    $p     = $item->candidate->player;
                                    $name  = $p?->localized('name');
                                    $club  = $p?->club?->localized('name');
                                    $photo = $p?->photo_path ? \Illuminate\Support\Facades\Storage::url($p->photo_path) : null;
                                @endphp
                                <div class="w-[110px] md:w-[140px] text-center">
                                    <div class="relative mx-auto w-20 h-20 md:w-24 md:h-24">
                                        <div class="w-full h-full rounded-full bg-gradient-to-br from-accent-400 to-accent-600 p-1 shadow-xl">
                                            <div class="w-full h-full rounded-full overflow-hidden bg-white flex items-center justify-center">
                                                @if($photo)
                                                    <img src="{{ $photo }}" alt="{{ $name }}" class="w-full h-full object-cover">
                                                @else
                                                    <span class="text-3xl font-black text-brand-700">{{ mb_strtoupper(mb_substr($name ?? '?', 0, 1)) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="absolute -top-1 -right-1 w-7 h-7 rounded-full bg-accent-500 text-white text-sm flex items-center justify-center font-black shadow-lg">★</div>
                                    </div>
                                    <div class="mt-2 text-white text-xs md:text-sm font-extrabold truncate">{{ $name }}</div>
                                    @if($club)
                                        <div class="text-[10px] md:text-[11px] text-brand-100/80 truncate">{{ $club }}</div>
                                    @endif
                                    <div class="mt-1 inline-flex items-center gap-1 rounded-full bg-accent-500/25 border border-accent-400/50 px-2 py-0.5 text-[10px] md:text-[11px] text-accent-300 font-bold">
                                        {{ number_format($item->votes_count) }} · {{ $item->vote_percentage }}%
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <footer class="text-center text-xs text-ink-500 pb-4">
        © {{ date('Y') }} {{ __('Saudi Football Players Association') }} — {{ __('Official winner announcement') }}
    </footer>
</div>

</body>
</html>
