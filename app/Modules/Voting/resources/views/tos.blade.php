@php($locale = app()->getLocale())
@php($dir = $locale === 'ar' ? 'rtl' : 'ltr')
<?php
    use App\Modules\Campaigns\Domain\TeamOfSeasonFormation as F;
    $defaultFormation = F::fromCampaign($campaign) ?: F::default();
?>
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $campaign->localized('title') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: '{{ $locale === 'ar' ? 'Tajawal' : 'Inter' }}', system-ui, sans-serif; }
        .candidate { cursor: pointer; transition: all .15s; }
        .candidate.selected { border-color: #059669 !important; background: #ecfdf5; box-shadow: 0 0 0 2px #10b981; transform: scale(1.02); }
        .candidate.disabled { opacity: .35; cursor: not-allowed; }
        .pitch { background: linear-gradient(to bottom, #047857, #065f46); position: relative; }
        .pitch::before { content: ''; position: absolute; inset: 0;
            background-image:
                linear-gradient(to right, rgba(255,255,255,.08) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255,255,255,.08) 1px, transparent 1px);
            background-size: 40px 40px; }
        .preset-btn.active { background: #059669; color: white; border-color: #059669; }
    </style>
</head>
<body class="bg-ink-50 text-ink-900">
<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

    <section class="rounded-3xl bg-gradient-to-{{ $dir === 'rtl' ? 'l' : 'r' }} from-ink-950 via-ink-900 to-brand-800 text-white p-8 md:p-10 shadow-2xl">
        <div class="text-brand-300 text-sm font-semibold">{{ __('Team of the Season') }}</div>
        <h1 class="text-3xl md:text-4xl font-bold mt-2">{{ $campaign->localized('title') }}</h1>
        @if($campaign->localized('description'))
            <p class="text-ink-200 mt-3 leading-7">{{ $campaign->localized('description') }}</p>
        @endif
    </section>

    @isset($voter)
        <div class="rounded-2xl bg-brand-50 border border-brand-200 text-brand-800 px-4 py-3 text-sm flex items-center gap-2">
            <span>✓</span>
            <span>{{ __('Verified as') }} {{ $voter['method'] === 'national_id' ? __('National ID') : __('Mobile') }}: <strong>{{ $voter['masked'] }}</strong></span>
        </div>
    @endisset

    @if($errors->any())
        <div class="rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 p-4">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    {{-- STEP 1: Voter picks formation --}}
    <section class="rounded-3xl border-2 border-brand-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold">{{ __('1. Choose your formation') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ __('Goalkeeper is always 1. The rest sums to 10 (attack + midfield + defense).') }}</p>
            </div>
            <div class="text-sm">
                <span class="text-gray-500">{{ __('Current') }}:</span>
                <span id="formationLabel" class="font-bold text-brand-700 text-lg">
                    {{ $defaultFormation['defense'] }}-{{ $defaultFormation['midfield'] }}-{{ $defaultFormation['attack'] }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="rounded-2xl bg-ink-50 p-4 text-center">
                <label class="text-xs text-gray-600 block mb-2">{{ __('Attack') }}</label>
                <input type="number" id="fAttack" value="{{ $defaultFormation['attack'] }}"
                       min="{{ F::MIN_LINE }}" max="{{ F::MAX_LINE }}"
                       class="w-full text-center text-3xl font-bold text-brand-600 rounded-xl border border-brand-300 px-2 py-2">
            </div>
            <div class="rounded-2xl bg-ink-50 p-4 text-center">
                <label class="text-xs text-gray-600 block mb-2">{{ __('Midfield') }}</label>
                <input type="number" id="fMidfield" value="{{ $defaultFormation['midfield'] }}"
                       min="{{ F::MIN_LINE }}" max="{{ F::MAX_LINE }}"
                       class="w-full text-center text-3xl font-bold text-brand-600 rounded-xl border border-brand-300 px-2 py-2">
            </div>
            <div class="rounded-2xl bg-ink-50 p-4 text-center">
                <label class="text-xs text-gray-600 block mb-2">{{ __('Defense') }}</label>
                <input type="number" id="fDefense" value="{{ $defaultFormation['defense'] }}"
                       min="{{ F::MIN_LINE }}" max="{{ F::MAX_LINE }}"
                       class="w-full text-center text-3xl font-bold text-brand-600 rounded-xl border border-brand-300 px-2 py-2">
            </div>
            <div class="rounded-2xl bg-ink-50 p-4 text-center opacity-60">
                <label class="text-xs text-gray-600 block mb-2">{{ __('Goalkeeper') }}</label>
                <div class="text-3xl font-bold text-brand-600 py-2">1</div>
                <div class="text-xs text-gray-400">{{ __('Fixed') }}</div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 text-xs">
            <span class="text-gray-600 self-center">{{ __('Presets') }}:</span>
            <button type="button" data-f="4,3,3" class="preset-btn rounded-full border px-3 py-1">4-3-3</button>
            <button type="button" data-f="3,4,3" class="preset-btn rounded-full border px-3 py-1">3-4-3</button>
            <button type="button" data-f="4,4,2" class="preset-btn rounded-full border px-3 py-1">4-4-2</button>
            <button type="button" data-f="3,5,2" class="preset-btn rounded-full border px-3 py-1">3-5-2</button>
            <button type="button" data-f="5,3,2" class="preset-btn rounded-full border px-3 py-1">5-3-2</button>
            <button type="button" data-f="4,5,1" class="preset-btn rounded-full border px-3 py-1">4-5-1</button>
        </div>

        <div id="formationStatus" class="mt-3 text-sm"></div>
    </section>

    {{-- STEP 2: Players section --}}
    <section id="playersSection" class="pitch rounded-3xl overflow-hidden shadow-2xl p-6 md:p-10 hidden">
        <div class="relative z-10 space-y-8">
            <div class="text-center text-white text-lg font-bold">{{ __('2. Pick your players') }}</div>
            @foreach(['attack', 'midfield', 'defense', 'goalkeeper'] as $slot)
                <?php $cat = $campaign->categories->firstWhere('position_slot', $slot); ?>
                @if($cat)
                    <div>
                        <div class="text-center text-white mb-3 font-semibold">
                            {{ __(ucfirst($slot)) }}
                            <span class="text-brand-300">(<span class="line-counter-{{ $slot }}">0</span>/<span class="line-target-{{ $slot }}">{{ $defaultFormation[$slot] }}</span>)</span>
                        </div>
                        <div class="flex flex-wrap justify-center gap-3 md:gap-4" data-slot="{{ $slot }}">
                            @foreach($cat->candidates as $cand)
                                <?php
                                    $p = $cand->player;
                                    $name = $p?->localized('name');
                                    $club = $p?->club?->localized('name');
                                    $photo = $p?->photo_path;
                                ?>
                                <label class="candidate block w-36 rounded-2xl bg-white p-3 text-center border-2 border-transparent">
                                    <input type="checkbox" class="hidden cand-input" data-slot="{{ $slot }}" value="{{ $cand->id }}">
                                    <div class="w-16 h-16 mx-auto rounded-full bg-ink-100 overflow-hidden mb-2 flex items-center justify-center text-2xl">
                                        @if($photo)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($photo) }}" class="w-full h-full object-cover" alt="">
                                        @else
                                            🧍
                                        @endif
                                    </div>
                                    <div class="font-semibold text-sm text-gray-900 truncate">{{ $name }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ $club }}</div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </section>

    <form method="post" id="tosForm" action="{{ route('voting.submit', $campaign->public_token) }}" class="hidden">@csrf</form>

    <div class="sticky bottom-0 bg-white border-t border-gray-200 p-4 rounded-t-3xl shadow-lg flex items-center justify-between gap-4">
        <div id="summary" class="text-sm text-gray-600">{{ __('Set your formation first, then pick players.') }}</div>
        <button type="button" id="submitBtn" disabled
                class="rounded-2xl bg-brand-600 hover:bg-brand-700 text-white px-8 py-3 font-semibold disabled:bg-ink-300 disabled:cursor-not-allowed disabled:hover:bg-ink-300">
            {{ __('Submit my Team of the Season') }}
        </button>
    </div>
</div>

<script>
    const MIN = {{ F::MIN_LINE }};
    const MAX = {{ F::MAX_LINE }};
    const TARGET = {{ F::OUTFIELD_TOTAL }};

    const inAtt = document.getElementById('fAttack');
    const inMid = document.getElementById('fMidfield');
    const inDef = document.getElementById('fDefense');
    const label = document.getElementById('formationLabel');
    const statusEl = document.getElementById('formationStatus');
    const playersSection = document.getElementById('playersSection');
    const submitBtn = document.getElementById('submitBtn');
    const summary = document.getElementById('summary');

    const REQUIRED = { attack: 0, midfield: 0, defense: 0, goalkeeper: 1 };
    const selected = { attack: new Set(), midfield: new Set(), defense: new Set(), goalkeeper: new Set() };

    function currentFormation() {
        return {
            attack:   parseInt(inAtt.value, 10) || 0,
            midfield: parseInt(inMid.value, 10) || 0,
            defense:  parseInt(inDef.value, 10) || 0,
            goalkeeper: 1,
        };
    }

    function validFormation(f) {
        if (f.goalkeeper !== 1) return false;
        for (const s of ['attack','midfield','defense']) {
            if (f[s] < MIN || f[s] > MAX) return false;
        }
        return (f.attack + f.midfield + f.defense) === TARGET;
    }

    function onFormationChange() {
        const f = currentFormation();
        Object.assign(REQUIRED, f);
        label.textContent = `${f.defense}-${f.midfield}-${f.attack}`;
        ['attack','midfield','defense','goalkeeper'].forEach(slot => {
            document.querySelectorAll('.line-target-'+slot).forEach(e => e.textContent = REQUIRED[slot]);
        });

        if (validFormation(f)) {
            statusEl.innerHTML = `<span class="text-brand-700 font-semibold">✓ {{ __('Valid formation') }}: ${f.defense}-${f.midfield}-${f.attack}</span>`;
            playersSection.classList.remove('hidden');
        } else {
            const sum = f.attack + f.midfield + f.defense;
            statusEl.innerHTML = `<span class="text-danger-600 font-semibold">✗ {{ __('Outfield sum must equal') }} ${TARGET} ({{ __('currently') }} ${sum})</span>`;
            playersSection.classList.add('hidden');
        }

        // Trim over-selections if formation shrunk
        ['attack','midfield','defense'].forEach(slot => {
            while (selected[slot].size > REQUIRED[slot]) {
                const last = [...selected[slot]].pop();
                selected[slot].delete(last);
            }
            document.querySelectorAll(`[data-slot="${slot}"] .cand-input`).forEach(i => {
                if (!selected[slot].has(i.value)) {
                    i.checked = false;
                    i.closest('.candidate')?.classList.remove('selected');
                }
            });
        });
        update();
    }

    [inAtt, inMid, inDef].forEach(e => e.addEventListener('input', onFormationChange));
    document.querySelectorAll('.preset-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const [d, m, a] = btn.dataset.f.split(',').map(Number);
            inDef.value = d; inMid.value = m; inAtt.value = a;
            document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            onFormationChange();
        });
    });

    document.querySelectorAll('.candidate').forEach(labelEl => {
        const input = labelEl.querySelector('.cand-input');
        const slot = input.dataset.slot;
        labelEl.addEventListener('click', (e) => {
            if (e.target === input) return;
            e.preventDefault();
            if (labelEl.classList.contains('disabled') && !input.checked) return;
            if (input.checked) { selected[slot].delete(input.value); input.checked = false; }
            else if (selected[slot].size < REQUIRED[slot]) { selected[slot].add(input.value); input.checked = true; }
            else return;
            labelEl.classList.toggle('selected', input.checked);
            update();
        });
    });

    function update() {
        ['attack','midfield','defense','goalkeeper'].forEach(slot => {
            document.querySelectorAll('.line-counter-'+slot).forEach(e => e.textContent = selected[slot].size);
            document.querySelectorAll(`[data-slot="${slot}"] .candidate`).forEach(l => {
                const i = l.querySelector('.cand-input');
                const isFull = selected[slot].size >= REQUIRED[slot];
                l.classList.toggle('disabled', isFull && !i.checked);
            });
        });
        const f = currentFormation();
        const fOk = validFormation(f);
        const complete = fOk &&
            selected.attack.size === REQUIRED.attack &&
            selected.midfield.size === REQUIRED.midfield &&
            selected.defense.size === REQUIRED.defense &&
            selected.goalkeeper.size === 1;
        submitBtn.disabled = !complete;
        if (fOk) {
            const got = selected.attack.size + selected.midfield.size + selected.defense.size + selected.goalkeeper.size;
            summary.textContent = `${got} / 11 — ${f.defense}-${f.midfield}-${f.attack}`;
        }
    }

    document.getElementById('submitBtn').addEventListener('click', () => {
        const form = document.getElementById('tosForm');
        [...form.querySelectorAll('input[type="hidden"]:not([name="_token"])')].forEach(e => e.remove());
        Object.entries(selected).forEach(([slot, ids]) => {
            ids.forEach(id => {
                const i = document.createElement('input');
                i.type = 'hidden'; i.name = `${slot}[]`; i.value = id;
                form.appendChild(i);
            });
        });
        form.submit();
    });

    onFormationChange();
</script>
</body>
</html>
