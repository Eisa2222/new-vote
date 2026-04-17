@php
    use App\Modules\Campaigns\Domain\TeamOfSeasonFormation as F;

    $locale = app()->getLocale();
    $dir    = $locale === 'ar' ? 'rtl' : 'ltr';

    $formation = F::fromCampaign($campaign) ?: F::default();
    $totalRequired = array_sum($formation);

    // Build lookups.
    //  - candidatesLookup: by candidate id → {id, name, club_id, club_name, club_logo, position, photo, jersey}
    //  - clubsByPosition: per slot → list of clubs that have at least one candidate,
    //                     with count and logo. Used for the club step of the modal.
    $candidatesLookup = [];
    $clubsByPosition  = [
        'goalkeeper' => [], 'defense' => [], 'midfield' => [], 'attack' => [],
    ];
    $clubIndex = [];  // position → [clubId => row], used to dedup & count

    foreach ($campaign->categories as $cat) {
        $slot = $cat->position_slot;
        if (!isset($formation[$slot])) continue;

        foreach ($cat->candidates as $cand) {
            $p  = $cand->player;
            if (!$p) continue;
            $club = $p->club;
            $clubId   = $club?->id ?? 0;
            $clubName = $club?->localized('name') ?? __('Unknown club');
            $clubLogo = $club?->logo_path
                ? \Illuminate\Support\Facades\Storage::url($club->logo_path)
                : null;

            $candidatesLookup[$cand->id] = [
                'id'        => $cand->id,
                'name'      => $p->localized('name') ?? '—',
                'photo'     => $p->photo_path
                    ? \Illuminate\Support\Facades\Storage::url($p->photo_path)
                    : null,
                'jersey'    => $p->jersey_number,
                'position'  => $slot,
                'club_id'   => $clubId,
                'club_name' => $clubName,
                'club_logo' => $clubLogo,
            ];

            $clubIndex[$slot][$clubId] = [
                'id'    => $clubId,
                'name'  => $clubName,
                'logo'  => $clubLogo,
                'count' => ($clubIndex[$slot][$clubId]['count'] ?? 0) + 1,
            ];
        }
    }
    foreach ($clubIndex as $slot => $clubs) {
        $clubsByPosition[$slot] = array_values($clubs);
    }

    $slotMeta = [
        'goalkeeper' => ['icon' => '🧤', 'color' => 'from-amber-500 to-amber-600',    'label' => __('Goalkeeper')],
        'defense'    => ['icon' => '🛡️', 'color' => 'from-blue-600 to-blue-700',      'label' => __('Defense')],
        'midfield'   => ['icon' => '⚙️', 'color' => 'from-emerald-600 to-emerald-700', 'label' => __('Midfield')],
        'attack'     => ['icon' => '⚡', 'color' => 'from-rose-600 to-rose-700',      'label' => __('Attack')],
    ];
    $displayOrder = ['attack', 'midfield', 'defense', 'goalkeeper'];
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
        /* Pitch surface — layered green gradient + faint horizontal stripes.
           Uses inline CSS instead of utility classes so Tailwind's CDN
           purge never strips it. */
        .pitch-surface {
            background-color: #0B3D2E;
            background-image:
                repeating-linear-gradient(to bottom, rgba(255,255,255,0.05) 0 48px, transparent 48px 96px),
                radial-gradient(ellipse at center, rgba(255,255,255,0.08) 0%, transparent 60%),
                linear-gradient(180deg, #1F7A49 0%, #115C42 55%, #0B3D2E 100%);
        }
    </style>
</head>
<body class="bg-ink-50 text-ink-900 min-h-screen">

<div x-data="tosVote({
        formation:       {{ json_encode($formation) }},
        totalRequired:   {{ $totalRequired }},
        candidates:      {{ json_encode($candidatesLookup) }},
        clubsByPosition: {{ json_encode($clubsByPosition) }}
    })"
     class="max-w-5xl mx-auto px-3 md:px-6 py-6 space-y-5 pb-36">

    {{-- HERO --}}
    <x-team-of-season.campaign-header
        :campaign="$campaign"
        :formation="$formation"
        :totalRequired="$totalRequired"
        :voter="$voter ?? null" />

    @if($errors->any())
        <div class="rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 p-4">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    {{-- PITCH — clickable slots; filled slots show the player, empty show + --}}
    <section class="relative rounded-3xl overflow-hidden shadow-brand border border-brand-900/30">
        <div class="pitch-surface relative py-8 md:py-12 px-4">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute inset-x-0 top-1/2 h-px bg-white/30"></div>
                <div class="absolute left-1/2 top-1/2 w-24 h-24 md:w-36 md:h-36 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white/25"></div>
                <div class="absolute inset-x-10 top-2 h-10 border-2 border-b-0 border-white/25 rounded-t-xl"></div>
                <div class="absolute inset-x-10 bottom-2 h-10 border-2 border-t-0 border-white/25 rounded-b-xl"></div>
            </div>

            <div class="relative space-y-6 md:space-y-10">
                @foreach(['attack','midfield','defense','goalkeeper'] as $slot)
                    <div class="flex items-center justify-center gap-2 md:gap-5 flex-wrap">
                        <template x-for="i in {{ $formation[$slot] }}" :key="'{{ $slot }}-'+i">
                            <button type="button"
                                    @click="slotClicked('{{ $slot }}', i-1)"
                                    class="group relative w-[74px] md:w-[100px] text-center focus:outline-none">
                                {{-- FILLED --}}
                                <template x-if="selected['{{ $slot }}'][i-1]">
                                    <span class="block">
                                        <span class="relative mx-auto block w-[60px] h-[60px] md:w-[74px] md:h-[74px] rounded-full bg-white shadow-xl ring-4 ring-accent-400 overflow-hidden transition group-hover:ring-accent-500">
                                            <template x-if="photoFor(selected['{{ $slot }}'][i-1])">
                                                <img :src="photoFor(selected['{{ $slot }}'][i-1])"
                                                     :alt="nameFor(selected['{{ $slot }}'][i-1])"
                                                     class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!photoFor(selected['{{ $slot }}'][i-1])">
                                                <span class="w-full h-full flex items-center justify-center text-brand-700 font-extrabold text-2xl"
                                                      x-text="(nameFor(selected['{{ $slot }}'][i-1]) || '?').charAt(0)"></span>
                                            </template>
                                            <span class="absolute -top-1 -end-1 w-6 h-6 rounded-full bg-rose-600 text-white text-xs font-bold flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow-lg">×</span>
                                        </span>
                                        <span class="block mt-1.5 text-white text-[11px] md:text-xs font-bold truncate max-w-[80px] md:max-w-[100px] mx-auto"
                                              x-text="nameFor(selected['{{ $slot }}'][i-1])"></span>
                                        <span class="block text-[10px] md:text-[11px] text-brand-100/80 truncate max-w-[80px] md:max-w-[100px] mx-auto"
                                              x-text="clubNameFor(selected['{{ $slot }}'][i-1])"></span>
                                    </span>
                                </template>

                                {{-- EMPTY --}}
                                <template x-if="!selected['{{ $slot }}'][i-1]">
                                    <span class="block">
                                        <span class="mx-auto flex items-center justify-center w-[60px] h-[60px] md:w-[74px] md:h-[74px] rounded-full border-2 border-dashed border-white/60 bg-white/5 backdrop-blur-sm text-white/80 group-hover:border-accent-400 group-hover:text-accent-400 group-hover:bg-white/10 transition">
                                            <span class="text-xl md:text-2xl font-bold">+</span>
                                        </span>
                                        <span class="block mt-1.5 text-white/85 text-[10px] md:text-[11px] font-semibold">
                                            {{ __(ucfirst($slot)) }}
                                        </span>
                                    </span>
                                </template>
                            </button>
                        </template>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Tiny counter pill for glance-able progress --}}
    <x-team-of-season.selection-counter
        :formation="$formation"
        :totalRequired="$totalRequired" />

    {{-- Alert bubble --}}
    <template x-if="alertMsg">
        <div class="rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 p-3 text-sm font-semibold flex items-center gap-2">
            <span>&#9888;</span><span x-text="alertMsg"></span>
        </div>
    </template>

    {{-- Submit form (hidden inputs built on submit) --}}
    <form method="post" action="{{ route('voting.submit', $campaign->public_token) }}" id="tosForm"
          @submit.prevent="submitVote($event)">
        @csrf
        <div id="hiddenInputs"></div>
        <x-team-of-season.submit-bar :totalRequired="$totalRequired" />
    </form>

    {{-- MODAL — drill down: position → club → player --}}
    <template x-teleport="body">
        <div x-show="modal.open" x-cloak
             @keydown.escape.window="closeModal()"
             class="fixed inset-0 z-[70] flex items-end md:items-center justify-center p-0 md:p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal()"></div>

            <div class="relative w-full md:max-w-2xl md:rounded-3xl rounded-t-3xl bg-white shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">

                {{-- Modal header changes with the current step --}}
                <header :class="headerColor()" class="px-5 py-4 text-white flex items-center gap-3 transition-colors">
                    <button type="button"
                            x-show="modal.step === 'player'"
                            @click="backToClubs()"
                            class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center text-lg">
                        <span x-text="'{{ $dir === 'rtl' ? '›' : '‹' }}'"></span>
                    </button>
                    <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-2xl"
                         x-text="iconFor(modal.position)"></div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs uppercase tracking-wider text-white/70 font-bold">
                            <template x-if="modal.step === 'club'">
                                <span>{{ __('Step 1 — pick a club') }}</span>
                            </template>
                            <template x-if="modal.step === 'player'">
                                <span x-text="'{{ __('Step 2 — pick a player from') }} '+ (currentClub()?.name || '')"></span>
                            </template>
                        </div>
                        <h3 class="text-xl font-extrabold leading-tight" x-text="labelFor(modal.position)"></h3>
                    </div>
                    <button type="button" @click="closeModal()"
                            class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center text-lg">×</button>
                </header>

                {{-- Search bar --}}
                <div class="p-4 border-b border-ink-100">
                    <input type="text" x-model="modal.query"
                           :placeholder="modal.step === 'club' ? '{{ __('Search club…') }}' : '{{ __('Search player…') }}'"
                           class="w-full rounded-xl border border-ink-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                {{-- STEP 1: club list --}}
                <div x-show="modal.step === 'club'" class="overflow-y-auto flex-1 p-4">
                    <template x-if="visibleClubs().length === 0">
                        <div class="text-center text-ink-500 py-10 text-sm">
                            {{ __('No clubs match your search.') }}
                        </div>
                    </template>
                    <div class="grid grid-cols-2 gap-3">
                        <template x-for="club in visibleClubs()" :key="'c-'+club.id">
                            <button type="button" @click="pickClub(club.id)"
                                    class="rounded-2xl border-2 border-ink-200 bg-white p-4 text-start hover:border-brand-500 hover:shadow transition flex items-center gap-3">
                                <template x-if="club.logo">
                                    <img :src="club.logo" :alt="club.name" class="w-12 h-12 rounded-xl object-cover border border-ink-200">
                                </template>
                                <template x-if="!club.logo">
                                    <div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center text-xl font-extrabold"
                                         x-text="(club.name || '?').charAt(0)"></div>
                                </template>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-sm text-ink-900 truncate" x-text="club.name"></div>
                                    <div class="text-xs text-ink-500">
                                        <span x-text="club.count"></span> {{ __('players available') }}
                                    </div>
                                </div>
                                <span class="text-ink-400 text-xl">{{ $dir === 'rtl' ? '‹' : '›' }}</span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- STEP 2: player list for chosen club --}}
                <div x-show="modal.step === 'player'" class="overflow-y-auto flex-1 p-4">
                    <template x-if="visiblePlayers().length === 0">
                        <div class="text-center text-ink-500 py-10 text-sm">
                            {{ __('No players in this line from this club.') }}
                        </div>
                    </template>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="cand in visiblePlayers()" :key="'p-'+cand.id">
                            <button type="button"
                                    @click="pickPlayer(cand.id)"
                                    :disabled="isAlreadyPicked(cand.id)"
                                    :class="isAlreadyPicked(cand.id)
                                        ? 'border-ink-200 bg-ink-50 opacity-60 cursor-not-allowed'
                                        : 'border-ink-200 hover:border-brand-500 hover:shadow bg-white'"
                                    class="relative rounded-2xl border-2 p-3 text-start transition flex items-center gap-3">
                                <template x-if="cand.photo">
                                    <img :src="cand.photo" :alt="cand.name" class="w-12 h-12 rounded-full object-cover border border-ink-200">
                                </template>
                                <template x-if="!cand.photo">
                                    <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xl font-extrabold"
                                         x-text="(cand.name || '?').charAt(0)"></div>
                                </template>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-sm truncate" x-text="cand.name"></div>
                                    <div class="text-xs text-ink-500 truncate">
                                        <span x-text="cand.club_name"></span>
                                        <template x-if="cand.jersey">
                                            <span class="ms-1 inline-block rounded bg-ink-100 text-ink-600 px-1.5 py-0.5 text-[10px] font-bold">#<span x-text="cand.jersey"></span></span>
                                        </template>
                                    </div>
                                </div>
                                <template x-if="isAlreadyPicked(cand.id)">
                                    <span class="text-xs font-bold text-emerald-700 whitespace-nowrap">✓ {{ __('Picked') }}</span>
                                </template>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>

</div>

<script>
function tosVote({ formation, totalRequired, candidates, clubsByPosition }) {
    const colors = {
        goalkeeper: 'bg-gradient-to-r from-amber-500 to-amber-600',
        defense:    'bg-gradient-to-r from-blue-600 to-blue-700',
        midfield:   'bg-gradient-to-r from-emerald-600 to-emerald-700',
        attack:     'bg-gradient-to-r from-rose-600 to-rose-700',
    };
    const icons  = { goalkeeper:'🧤', defense:'🛡️', midfield:'⚙️', attack:'⚡' };
    const labels = {
        goalkeeper: '{{ __("Goalkeeper") }}',
        defense:    '{{ __("Defense") }}',
        midfield:   '{{ __("Midfield") }}',
        attack:     '{{ __("Attack") }}',
    };

    return {
        formation,
        totalRequired,
        candidates,
        clubsByPosition,
        selected: { attack: [], midfield: [], defense: [], goalkeeper: [] },
        submitting: false,
        alertMsg: '',
        modal: { open: false, position: null, step: 'club', clubId: null, targetIndex: null, query: '' },

        // ── pitch interactions ─────────────────────────
        slotClicked(pos, idx) {
            // Filled slot → remove
            if (this.selected[pos][idx]) {
                this.selected[pos].splice(idx, 1);
                return;
            }
            // Empty slot → open modal drill-down
            this.openModal(pos, idx);
        },

        // ── modal state ────────────────────────────────
        openModal(pos, targetIdx) {
            this.modal = {
                open: true, position: pos, step: 'club',
                clubId: null, targetIndex: targetIdx, query: ''
            };
            document.body.classList.add('overflow-hidden');
        },
        closeModal() {
            this.modal.open = false;
            document.body.classList.remove('overflow-hidden');
        },
        backToClubs() {
            this.modal.step  = 'club';
            this.modal.clubId = null;
            this.modal.query = '';
        },
        pickClub(clubId) {
            this.modal.clubId = clubId;
            this.modal.step   = 'player';
            this.modal.query  = '';
        },
        pickPlayer(candId) {
            const pos = this.modal.position;
            if (this.isAlreadyPicked(candId)) return;

            // Enforce line capacity — swap oldest if full
            if (this.selected[pos].length >= this.formation[pos]) {
                this.selected[pos].shift();
                this.flash(
                    '{{ __("Line :label was full — oldest pick replaced.") }}'
                        .replace(':label', labels[pos] || pos)
                );
            }
            this.selected[pos].push(candId);
            this.closeModal();
        },

        currentClub() {
            if (this.modal.clubId == null) return null;
            return (this.clubsByPosition[this.modal.position] || [])
                   .find(c => c.id === this.modal.clubId) || null;
        },

        // ── derived lists for modal ────────────────────
        visibleClubs() {
            const q = this.modal.query.trim().toLowerCase();
            return (this.clubsByPosition[this.modal.position] || [])
                   .filter(c => !q || (c.name || '').toLowerCase().includes(q));
        },
        visiblePlayers() {
            const q   = this.modal.query.trim().toLowerCase();
            const pos = this.modal.position;
            const cid = this.modal.clubId;
            return Object.values(this.candidates).filter(c =>
                c.position === pos &&
                c.club_id  === cid &&
                (!q || (c.name || '').toLowerCase().includes(q))
            );
        },

        isAlreadyPicked(id) {
            return Object.values(this.selected).some(arr => arr.includes(id));
        },

        // ── helpers used in templates ──────────────────
        nameFor(id)     { return this.candidates[id]?.name || ''; },
        photoFor(id)    { return this.candidates[id]?.photo || ''; },
        clubNameFor(id) { return this.candidates[id]?.club_name || ''; },
        iconFor(pos)    { return icons[pos] || ''; },
        labelFor(pos)   { return labels[pos] || pos; },
        headerColor()   { return colors[this.modal.position] || 'bg-brand-700'; },

        totalSelected() {
            return Object.values(this.selected).reduce((a, ids) => a + ids.length, 0);
        },
        lineOk(pos) { return this.selected[pos].length === this.formation[pos]; },
        canSubmit() { return this.totalSelected() === this.totalRequired; },

        missingSummary() {
            const missing = [];
            for (const [pos, n] of Object.entries(this.formation)) {
                const got = this.selected[pos].length;
                if (got !== n) missing.push(`${labels[pos]}: ${got}/${n}`);
            }
            return missing.length ? '{{ __("Incomplete") }} — ' + missing.join(' · ') : '';
        },

        flash(msg) {
            this.alertMsg = msg;
            clearTimeout(this._t);
            this._t = setTimeout(() => { this.alertMsg = ''; }, 2500);
        },

        submitVote(e) {
            if (!this.canSubmit() || this.submitting) return;
            this.submitting = true;
            const form = e.target;
            const holder = form.querySelector('#hiddenInputs');
            holder.innerHTML = '';
            for (const [pos, ids] of Object.entries(this.selected)) {
                ids.forEach(v => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `${pos}[]`;
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
