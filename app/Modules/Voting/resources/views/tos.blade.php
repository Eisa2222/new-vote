@php
    use App\Modules\Campaigns\Domain\TeamOfSeasonFormation as F;

    $locale = app()->getLocale();
    $dir    = $locale === 'ar' ? 'rtl' : 'ltr';

    $formation = F::fromCampaign($campaign) ?: F::default();
    $totalRequired = array_sum($formation);

    // Fixed, predictable display order — goalkeeper at the top (closest to pitch bottom,
    // so the user's eye follows pitch → GK section → Defense → Midfield → Attack).
    $displayOrder = ['goalkeeper', 'defense', 'midfield', 'attack'];

    $candidatesBySlot = [];
    $lookup = [];
    foreach ($campaign->categories as $cat) {
        if (! array_key_exists($cat->position_slot, $formation)) continue;
        $candidatesBySlot[$cat->position_slot] = $cat->candidates;
        foreach ($cat->candidates as $cand) {
            $p = $cand->player;
            $lookup[$cand->id] = [
                'name'  => $p?->localized('name') ?? '—',
                'club'  => $p?->club?->localized('name') ?? '',
                'slot'  => $cat->position_slot, // defensive: UI uses this to refuse cross-slot adds
                'photo' => $p?->photo_path
                    ? \Illuminate\Support\Facades\Storage::url($p->photo_path)
                    : 'data:image/svg+xml;utf8,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 72 72"><rect width="72" height="72" fill="#ECF5EF"/><text x="50%" y="55%" text-anchor="middle" font-size="32" fill="#115C42" font-family="sans-serif">'.htmlspecialchars(mb_substr($p?->localized('name') ?? '?', 0, 1), ENT_QUOTES).'</text></svg>'),
            ];
        }
    }

    $slotIcons = [
        'goalkeeper' => '🧤',
        'defense'    => '🛡️',
        'midfield'   => '⚙️',
        'attack'     => '⚡',
    ];
    $slotColors = [
        'goalkeeper' => 'from-amber-500 to-amber-600',
        'defense'    => 'from-blue-600 to-blue-700',
        'midfield'   => 'from-emerald-600 to-emerald-700',
        'attack'     => 'from-rose-600 to-rose-700',
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $campaign->localized('title') }}</title>
    @include('partials.brand-head')
    <script defer src="https://unpkg.com/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .panel-highlight { animation: pulseRing 1.2s ease-out 1; }
        @keyframes pulseRing {
            0%   { box-shadow: 0 0 0 0 rgba(17, 92, 66, 0.6); }
            100% { box-shadow: 0 0 0 16px rgba(17, 92, 66, 0); }
        }
    </style>
</head>
<body class="bg-ink-50 text-ink-900 min-h-screen">

<div x-data="tosBoard({
        formation: {{ json_encode($formation) }},
        totalRequired: {{ $totalRequired }},
        candidates: {{ json_encode($lookup) }}
    })"
     class="max-w-6xl mx-auto px-3 md:px-6 py-6 space-y-6 pb-32">

    {{-- Hero --}}
    <x-team-of-season.campaign-header
        :campaign="$campaign"
        :formation="$formation"
        :totalRequired="$totalRequired"
        :voter="$voter ?? null" />

    {{-- Server-side validation errors --}}
    @if($errors->any())
        <div class="rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 p-4">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    {{-- Formation board (pitch visualisation) --}}
    <x-team-of-season.formation-board
        :formation="$formation"
        :candidatesBySlot="$candidatesBySlot" />

    {{-- Live selection counter --}}
    <x-team-of-season.selection-counter
        :formation="$formation"
        :totalRequired="$totalRequired" />

    {{-- Alert when selection is rejected (wrong line / line full) --}}
    <template x-if="alertMsg">
        <div class="rounded-2xl bg-rose-50 border border-rose-300 text-rose-800 p-4 font-semibold flex items-center gap-2">
            <span>&#9888;</span>
            <span x-text="alertMsg"></span>
        </div>
    </template>

    {{-- Submit form wraps the position sections. All panels are ALWAYS visible,
         one after the other, so a pick can never visually appear to "jump" lines. --}}
    <form method="post" action="{{ route('voting.submit', $campaign->public_token) }}" id="tosForm"
          @submit.prevent="submitVote($event)">
        @csrf
        <div id="hiddenInputs"></div>

        <div class="space-y-6">
            @foreach($displayOrder as $slot)
                @if(isset($formation[$slot]))
                    @php
                        $required   = $formation[$slot];
                        $candidates = $candidatesBySlot[$slot] ?? collect();
                        $label      = __(ucfirst($slot));
                    @endphp
                    <section id="panel-{{ $slot }}"
                             x-ref="panel_{{ $slot }}"
                             data-slot-panel="{{ $slot }}"
                             class="rounded-3xl bg-white border-2 border-ink-200 shadow-sm overflow-hidden scroll-mt-24"
                             :class="lineOk('{{ $slot }}') ? 'border-emerald-400 ring-2 ring-emerald-200' : ''">

                        {{-- Large, unmistakable position header --}}
                        <header class="px-5 md:px-6 py-4 md:py-5 bg-gradient-to-{{ $dir === 'rtl' ? 'l' : 'r' }} {{ $slotColors[$slot] }} text-white">
                            <div class="flex items-center justify-between gap-3 flex-wrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-2xl md:text-3xl">
                                        {{ $slotIcons[$slot] }}
                                    </div>
                                    <div>
                                        <div class="text-[11px] uppercase tracking-[0.2em] text-white/70">
                                            {{ __('Line') }} — {{ strtoupper($slot) }}
                                        </div>
                                        <h2 class="text-xl md:text-2xl font-extrabold leading-tight">
                                            {{ $label }}
                                        </h2>
                                        <div class="text-xs text-white/80 mt-0.5">
                                            {{ __('Pick exactly :n from :t candidates', ['n' => $required, 't' => $candidates->count()]) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="inline-flex items-center gap-2 rounded-full bg-white/15 backdrop-blur px-4 py-2 text-sm font-bold">
                                    <span x-text="selected['{{ $slot }}'].length"></span>
                                    <span class="text-white/70">/</span>
                                    <span>{{ $required }}</span>
                                    <span x-show="lineOk('{{ $slot }}')" class="ms-1">&#10003;</span>
                                </div>
                            </div>
                        </header>

                        <div class="p-3 md:p-5">
                            @if($candidates->isEmpty())
                                <div class="p-8 text-center text-ink-500 text-sm">
                                    {{ __('No candidates yet for this line.') }}
                                </div>
                            @else
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 md:gap-3 max-h-[460px] overflow-y-auto">
                                    @foreach($candidates as $cand)
                                        <x-team-of-season.player-card :position="$slot" :candidate="$cand" />
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>
                @endif
            @endforeach
        </div>

        <x-team-of-season.submit-bar :totalRequired="$totalRequired" />
    </form>
</div>

<script>
function tosBoard({ formation, totalRequired, candidates }) {
    return {
        formation,
        totalRequired,
        candidates,
        selected: { attack: [], midfield: [], defense: [], goalkeeper: [] },
        activePanel: 'all',
        submitting: false,
        alertMsg: '',

        /** Scroll to a line's section without hiding anything else. */
        openPanel(slot) {
            const el = document.getElementById('panel-' + slot);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                el.classList.add('panel-highlight');
                setTimeout(() => el.classList.remove('panel-highlight'), 1200);
            }
        },

        isSelected(slot, id) { return this.selected[slot].includes(id); },

        /** Toggle with strict slot locking. A candidate can ONLY ever be toggled
         *  on its own slot — this prevents a goalkeeper card from ending up under
         *  defense even in case of DOM re-ordering. */
        toggle(slot, id) {
            const lookup = this.candidates[id];
            if (lookup && lookup.slot && lookup.slot !== slot) {
                this.flash('{{ __("This player belongs to another line.") }}');
                return;
            }

            const arr = this.selected[slot];
            const idx = arr.indexOf(id);
            if (idx !== -1) {
                arr.splice(idx, 1);
                return;
            }
            if (arr.length >= this.formation[slot]) {
                // Replace the oldest pick (LRU) AND inform the user visibly.
                arr.shift();
                this.flash(
                    '{{ __("Line :label is full — replaced the oldest pick.") }}'
                        .replace(':label', this.labelFor(slot))
                );
            }
            arr.push(id);
        },

        flash(msg) {
            this.alertMsg = msg;
            clearTimeout(this._t);
            this._t = setTimeout(() => { this.alertMsg = ''; }, 2500);
        },

        candidateName(id)  { return this.candidates[id]?.name  || ''; },
        candidateClub(id)  { return this.candidates[id]?.club  || ''; },
        candidatePhoto(id) { return this.candidates[id]?.photo || ''; },

        totalSelected() {
            return Object.values(this.selected).reduce((a, ids) => a + ids.length, 0);
        },
        lineOk(slot) { return this.selected[slot].length === this.formation[slot]; },
        canSubmit()  { return this.totalSelected() === this.totalRequired; },

        missingSummary() {
            const missing = [];
            for (const [slot, n] of Object.entries(this.formation)) {
                const got = this.selected[slot].length;
                if (got !== n) missing.push(`${this.labelFor(slot)}: ${got}/${n}`);
            }
            return missing.length ? '{{ __("Incomplete") }} — ' + missing.join(' · ') : '';
        },

        labelFor(slot) {
            return ({
                attack:     '{{ __("Attack") }}',
                midfield:   '{{ __("Midfield") }}',
                defense:    '{{ __("Defense") }}',
                goalkeeper: '{{ __("Goalkeeper") }}',
            })[slot] || slot;
        },

        submitVote(e) {
            if (!this.canSubmit() || this.submitting) return;
            this.submitting = true;
            const form = e.target;
            const holder = form.querySelector('#hiddenInputs');
            holder.innerHTML = '';
            for (const [slot, ids] of Object.entries(this.selected)) {
                ids.forEach(v => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `${slot}[]`;
                    input.value = v;
                    holder.appendChild(input);
                });
            }
            form.submit();
        },
    };
}
</script>
</body>
</html>
