@extends('layouts.admin')

@section('title', __('TOTS candidates'))
@section('page_title', $campaign->localized('title'))
@section('page_description', __('Attach players to each line. Only players whose position matches the line can be attached.'))

@section('content')
@php
    $slotMeta = [
        'goalkeeper' => ['icon' => '🧤', 'color' => 'from-amber-500 to-amber-600',    'label' => __('Goalkeeper')],
        'defense'    => ['icon' => '🛡️', 'color' => 'from-blue-600 to-blue-700',      'label' => __('Defense')],
        'midfield'   => ['icon' => '⚙️', 'color' => 'from-emerald-600 to-emerald-700', 'label' => __('Midfield')],
        'attack'     => ['icon' => '⚡', 'color' => 'from-rose-600 to-rose-700',      'label' => __('Attack')],
    ];
    $displayOrder = ['goalkeeper', 'defense', 'midfield', 'attack'];
@endphp

<div class="flex items-center gap-2 text-sm text-ink-500 mb-6">
    <a href="/admin/campaigns" class="hover:underline">{{ __('Campaigns') }}</a>
    <span>·</span>
    <span>{{ __('Team of the Season candidates') }}</span>
</div>

<div x-data="{
        openSlot: null,
        open(slot) { this.openSlot = slot; document.body.classList.add('overflow-hidden'); },
        close() { this.openSlot = null; document.body.classList.remove('overflow-hidden'); }
    }"
     class="space-y-5">

    {{-- Position summary grid: one card per line, click to open modal --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($displayOrder as $slot)
            @php
                $category  = $campaign->categories->firstWhere('position_slot', $slot);
                if (!$category) continue;
                $attached  = $category->candidates->count();
                $available = $availableByPosition[$slot]->count();
                $meta      = $slotMeta[$slot];
            @endphp
            <button type="button" @click="open('{{ $slot }}')"
                    class="group rounded-3xl overflow-hidden border border-ink-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition text-start">
                <div class="bg-gradient-to-{{ app()->getLocale() === 'ar' ? 'l' : 'r' }} {{ $meta['color'] }} p-5 text-white">
                    <div class="flex items-center justify-between">
                        <div class="text-3xl">{{ $meta['icon'] }}</div>
                        <div class="text-xs uppercase tracking-wider text-white/70 font-bold">
                            {{ __('Required') }} {{ $category->required_picks }}
                        </div>
                    </div>
                    <div class="mt-3 text-lg font-extrabold leading-tight">{{ $meta['label'] }}</div>
                    <div class="text-xs text-white/80 mt-0.5">{{ $category->localized('title') }}</div>
                </div>
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-extrabold text-ink-900">{{ $attached }}</div>
                        <div class="text-[11px] text-ink-500 uppercase tracking-wider">{{ __('attached') }}</div>
                    </div>
                    <div class="text-end">
                        <div class="text-sm font-semibold text-brand-700">+ {{ $available }}</div>
                        <div class="text-[11px] text-ink-500">{{ __('available') }}</div>
                    </div>
                </div>
                <div class="px-4 pb-4">
                    <div class="text-xs text-brand-700 font-semibold group-hover:underline">
                        {{ __('Manage players') }} →
                    </div>
                </div>
            </button>
        @endforeach
    </div>

    {{-- Public link + back to campaign --}}
    <div class="rounded-3xl bg-brand-50 border border-brand-200 p-5 flex items-center justify-between gap-4 flex-wrap">
        <div class="text-brand-800 text-sm min-w-0">
            {{ __('Public link') }}:
            <a href="{{ url('/vote/'.$campaign->public_token) }}" target="_blank"
               class="font-mono text-xs underline break-all">{{ url('/vote/'.$campaign->public_token) }}</a>
        </div>
        <a href="/admin/campaigns/{{ $campaign->id }}"
           class="rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-5 py-2.5 font-medium whitespace-nowrap">
            {{ __('Go to campaign page') }}
        </a>
    </div>

    {{-- MODAL: small popup with the players for one position --}}
    <template x-teleport="body">
        <div x-show="openSlot" x-cloak
             class="fixed inset-0 z-[60] flex items-end md:items-center justify-center p-0 md:p-4"
             @keydown.escape.window="close()">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="close()"></div>

            @foreach($displayOrder as $slot)
                @php
                    $category = $campaign->categories->firstWhere('position_slot', $slot);
                    if (!$category) continue;
                    $meta     = $slotMeta[$slot];
                    $attached = $category->candidates;
                    $free     = $availableByPosition[$slot];
                @endphp
                <div x-show="openSlot === '{{ $slot }}'" x-cloak
                     class="relative w-full md:max-w-2xl md:rounded-3xl rounded-t-3xl bg-white shadow-2xl overflow-hidden flex flex-col max-h-[92vh]">
                    <header class="px-5 py-4 bg-gradient-to-{{ app()->getLocale() === 'ar' ? 'l' : 'r' }} {{ $meta['color'] }} text-white flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-2xl">{{ $meta['icon'] }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs uppercase tracking-wider text-white/70 font-bold">{{ __('Line') }}</div>
                            <h3 class="text-xl font-extrabold leading-tight">{{ $meta['label'] }}</h3>
                            <div class="text-xs text-white/80">{{ __('Required') }} {{ $category->required_picks }} · {{ $attached->count() }} {{ __('attached') }}</div>
                        </div>
                        <button type="button" @click="close()" class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center text-lg">&times;</button>
                    </header>

                    <div class="p-4 border-b border-ink-100">
                        <input type="text" placeholder="{{ __('Search player name or club…') }}"
                               @input="$event.target.closest('.relative').querySelectorAll('[data-player]').forEach(el => {
                                    const q = $event.target.value.trim().toLowerCase();
                                    el.style.display = !q || el.dataset.player.toLowerCase().includes(q) ? '' : 'none';
                                })"
                               class="w-full rounded-xl border border-ink-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div class="overflow-y-auto flex-1 divide-y divide-ink-100">
                        {{-- Attached players first — remove only --}}
                        @if($attached->isNotEmpty())
                            <div class="p-4">
                                <div class="text-xs font-bold uppercase text-ink-500 tracking-wider mb-2">
                                    {{ __('Attached') }} ({{ $attached->count() }})
                                </div>
                                <div class="space-y-2">
                                    @foreach($attached as $cand)
                                        @php $pl = $cand->player; @endphp
                                        <div data-player="{{ $pl?->name_ar }} {{ $pl?->name_en }} {{ $pl?->club?->name_ar }} {{ $pl?->club?->name_en }}"
                                             class="flex items-center gap-3 rounded-xl border border-brand-200 bg-brand-50 p-3">
                                            <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold">
                                                {{ mb_strtoupper(mb_substr($pl?->localized('name') ?? '?', 0, 1)) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-semibold text-sm truncate">{{ $pl?->localized('name') }}</div>
                                                <div class="text-xs text-ink-500 truncate">{{ $pl?->club?->localized('name') }} · #{{ $pl?->jersey_number }}</div>
                                            </div>
                                            <form method="post" action="/admin/candidates/{{ $cand->id }}" onsubmit="return confirm('{{ __('Remove?') }}')">
                                                @csrf @method('DELETE')
                                                <button class="rounded-lg border border-danger-500/40 text-danger-600 hover:bg-danger-500/10 px-3 py-1.5 text-xs font-semibold">
                                                    🗑 {{ __('Remove') }}
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Available players to attach --}}
                        <form method="post" action="/admin/tos/{{ $campaign->id }}/candidates" class="p-4">
                            @csrf
                            <div class="text-xs font-bold uppercase text-ink-500 tracking-wider mb-2 flex items-center justify-between">
                                <span>{{ __('Available to attach') }} ({{ $free->count() }})</span>
                                @if($free->isNotEmpty())
                                    <button type="button"
                                            onclick="this.closest('form').querySelectorAll('input[type=checkbox]').forEach(c => c.checked = !c.checked)"
                                            class="text-brand-700 normal-case tracking-normal hover:underline">
                                        {{ __('Toggle all') }}
                                    </button>
                                @endif
                            </div>

                            @if($free->isEmpty())
                                <div class="text-center text-ink-500 py-6 text-sm">
                                    {{ __('All matching players are already attached.') }}
                                </div>
                            @else
                                <div class="space-y-2">
                                    @foreach($free as $player)
                                        <label data-player="{{ $player->name_ar }} {{ $player->name_en }} {{ $player->club?->name_ar }} {{ $player->club?->name_en }}"
                                               class="flex items-center gap-3 rounded-xl border border-ink-200 bg-white p-3 cursor-pointer hover:border-brand-400 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 transition">
                                            <input type="checkbox" name="player_ids[]" value="{{ $player->id }}"
                                                   class="w-5 h-5 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                                            <div class="w-10 h-10 rounded-full bg-ink-100 text-ink-700 flex items-center justify-center font-bold">
                                                {{ mb_strtoupper(mb_substr($player->localized('name'), 0, 1)) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-semibold text-sm truncate">{{ $player->localized('name') }}</div>
                                                <div class="text-xs text-ink-500 truncate">{{ $player->club?->localized('name') }} · #{{ $player->jersey_number }}</div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                <button type="submit"
                                        class="w-full mt-4 rounded-xl bg-gradient-to-{{ app()->getLocale() === 'ar' ? 'l' : 'r' }} {{ $meta['color'] }} text-white py-3 font-bold shadow-brand hover:brightness-110">
                                    ✓ {{ __('Attach selected to :line', ['line' => $meta['label']]) }}
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </template>
</div>

@push('scripts')
<script defer src="https://unpkg.com/alpinejs@3.14.3/dist/cdn.min.js"></script>
@endpush
@endsection
