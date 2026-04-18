@extends('layouts.admin')
@section('content')
@push('scripts')
<script>
    (function () {
        const id = {{ $campaign->id }};
        async function poll() {
            try {
                const r = await fetch(`/admin/campaigns/${id}/stats`, { headers: { 'Accept': 'application/json' }});
                const { data } = await r.json();
                document.getElementById('liveVotes').textContent = data.votes_count;
                const bar = document.getElementById('liveBar');
                if (bar && data.percentage != null) bar.style.width = data.percentage + '%';
            } catch (e) {}
        }
        setInterval(poll, 7000);
    })();
</script>
@endpush
    <div class="flex items-start justify-between mb-6">
        <div>
            <a href="/admin/campaigns" class="text-sm text-slate-500 hover:underline">← {{ __('Campaigns') }}</a>
            <h1 class="text-2xl font-bold text-slate-800 mt-1">{{ $campaign->localized('title') }}</h1>
            <div class="mt-2 flex gap-2 items-center flex-wrap">
                @php
                    $typeLabels = [
                        'individual_award'   => __('Individual award'),
                        'team_award'         => __('Team award'),
                        'team_of_the_season' => __('Team of the Season'),
                    ];
                @endphp
                <span class="badge badge-{{ $campaign->status->value }} px-3 py-1">{{ $campaign->status->label() }}</span>
                <span class="text-sm text-slate-500">{{ $typeLabels[$campaign->type->value] ?? $campaign->type->value }}</span>
            </div>
        </div>
    </div>

    @if(in_array($campaign->status->value, ['draft', 'rejected']))
        <div class="rounded-3xl bg-gradient-to-r from-amber-50 to-emerald-50 border-2 border-amber-300 p-6 mb-6 shadow-sm">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl flex-shrink-0">
                        {{ $campaign->status->value === 'rejected' ? '❌' : '⚠️' }}
                    </div>
                    <div>
                        <h3 class="font-bold text-amber-900 text-lg">
                            @if($campaign->status->value === 'rejected')
                                {{ __('Rejected by committee') }}
                            @else
                                {{ __('This campaign is a draft') }}
                            @endif
                        </h3>
                        <p class="text-sm text-amber-800 mt-1">
                            {{ __('Submit the campaign to the committee for approval. Voting will be open only after approval.') }}
                        </p>
                        @if($campaign->committee_rejection_note)
                            <div class="mt-3 rounded-xl bg-rose-50 border border-rose-200 p-3 text-sm text-rose-800">
                                <strong>{{ __('Rejection note') }}:</strong> {{ $campaign->committee_rejection_note }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2 flex-shrink-0 flex-wrap">
                    <a href="/admin/campaigns/{{ $campaign->id }}/edit"
                       class="rounded-2xl border-2 border-amber-500 text-amber-700 hover:bg-amber-100 px-6 py-3 font-semibold">
                        ✏️ {{ __('Edit') }}
                    </a>
                    <form method="post" action="/admin/campaigns/{{ $campaign->id }}/submit-approval">
                        @csrf
                        <button class="rounded-2xl bg-brand-600 hover:bg-brand-700 text-white px-8 py-3 font-semibold text-lg shadow-brand">
                            📤 {{ __('Submit for committee approval') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($campaign->status->value === 'pending_approval')
        <div class="rounded-3xl bg-amber-50 border-2 border-amber-300 p-6 mb-6 shadow-sm">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl flex-shrink-0">⏳</div>
                    <div>
                        <h3 class="font-bold text-amber-900 text-lg">{{ __('Pending committee approval') }}</h3>
                        <p class="text-sm text-amber-800 mt-1">
                            {{ __('Awaiting review by a Voting Committee member before voting can be activated.') }}
                        </p>
                    </div>
                </div>

                @can('campaigns.approve')
                    <div class="flex gap-2 flex-wrap">
                        <form method="post" action="/admin/campaigns/{{ $campaign->id }}/approve">
                            @csrf
                            <button class="rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 font-bold shadow">
                                ✓ {{ __('Approve') }}
                            </button>
                        </form>
                        <form method="post" action="/admin/campaigns/{{ $campaign->id }}/reject"
                              onsubmit="this.querySelector('[name=reason]').value = prompt('{{ __('Reason for rejection (optional):') }}') || ''">
                            @csrf
                            <input type="hidden" name="reason">
                            <button class="rounded-2xl border-2 border-rose-400 text-rose-700 hover:bg-rose-50 px-6 py-3 font-bold">
                                ✗ {{ __('Reject') }}
                            </button>
                        </form>
                    </div>
                @endcan
            </div>
        </div>
    @endif

    @if($campaign->status->value === 'published')
        <div class="rounded-3xl bg-blue-50 border border-blue-200 p-5 mb-6 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⏰</span>
                <div>
                    <div class="font-semibold text-blue-900">{{ __('Published — waiting for start time') }}</div>
                    <div class="text-sm text-blue-700">{{ __('Start at') }}: {{ $campaign->start_at->format('Y-m-d H:i') }}</div>
                </div>
            </div>
            <form method="post" action="/admin/campaigns/{{ $campaign->id }}/activate">
                @csrf
                <button class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 font-semibold">
                    ⚡ {{ __('Activate now') }}
                </button>
            </form>
        </div>
    @endif

    @if(in_array($campaign->status->value, ['active','published']))
        <div class="flex gap-2 mb-6">
            <form method="post" action="/admin/campaigns/{{ $campaign->id }}/close">@csrf
                <button class="rounded-xl text-rose-700 border border-rose-300 hover:bg-rose-50 px-4 py-2 font-medium">
                    🛑 {{ __('Close campaign') }}
                </button>
            </form>
            <form method="post" action="/admin/campaigns/{{ $campaign->id }}/archive">@csrf
                <button class="rounded-xl text-slate-600 border border-slate-300 hover:bg-slate-50 px-4 py-2 font-medium"
                        onclick="return confirm('{{ __('Archive this campaign?') }}')">
                    📁 {{ __('Archive') }}
                </button>
            </form>
        </div>
    @endif

    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow p-4">
            <div class="text-slate-500 text-sm">{{ __('Votes') }}</div>
            <div id="liveVotes" class="text-2xl font-bold text-emerald-600">{{ $campaign->votes_count }}</div>
            @if($campaign->max_voters)
                <div class="mt-2 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                    <div id="liveBar" class="h-full bg-emerald-500 rounded-full" style="width: {{ min(100, round(($campaign->votes_count / $campaign->max_voters) * 100)) }}%"></div>
                </div>
            @endif
        </div>
        <div class="bg-white rounded-2xl shadow p-4">
            <div class="text-slate-500 text-sm">{{ __('Max voters') }}</div>
            <div class="text-2xl font-bold">{{ $campaign->max_voters ?? '∞' }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow p-4 col-span-2">
            <div class="text-slate-500 text-sm">{{ __('Public voting link') }}</div>
            <div class="mt-1 flex items-center gap-2">
                <input id="publink" type="text" readonly value="{{ url('/vote/'.$campaign->public_token) }}"
                       class="flex-1 border rounded px-2 py-1 text-sm">
                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('publink').value); this.innerText='✓'"
                        class="btn-ghost text-sm">{{ __('Copy') }}</button>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-2 mb-4">
        <a href="/admin/campaigns/{{ $campaign->id }}/categories"
           class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 font-medium">
            + {{ __('Manage categories & candidates') }}
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="font-semibold text-slate-800 mb-4">{{ __('Categories') }}</h2>
        @foreach($campaign->categories as $cat)
            <div class="border rounded-lg p-4 mb-3">
                <div class="flex justify-between items-center">
                    <h3 class="font-medium">{{ $cat->localized('title') }}</h3>
                    <span class="text-xs text-slate-500">
                        {{ __('Pick exactly :n', ['n' => $cat->required_picks]) }}
                        @if($cat->position_slot !== 'any') · {{ __(ucfirst($cat->position_slot)) }} @endif
                    </span>
                </div>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2">
                    @foreach($cat->candidates as $cand)
                        <div class="text-sm text-slate-600 border rounded p-2">
                            {{ $cand->player?->localized('name') ?? $cand->club?->localized('name') }}
                            @if($cand->player)<span class="text-xs text-slate-400">({{ $cand->player->club?->localized('name') }})</span>@endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @can('campaigns.delete', $campaign)
        {{-- Danger zone: delete campaign permanently. Kept at the bottom and
             visually separated so it isn't confused with everyday actions. --}}
        <div class="mt-8 rounded-3xl border-2 border-rose-200 bg-rose-50 p-6">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h3 class="font-bold text-rose-900">🗑️ {{ __('Danger zone') }}</h3>
                    <p class="text-sm text-rose-800 mt-1">
                        {{ __('Deleting removes the campaign, its categories, candidates, votes and result. This cannot be undone.') }}
                    </p>
                </div>
                <form method="post" action="/admin/campaigns/{{ $campaign->id }}"
                      onsubmit="return confirm('{{ __('Permanently delete :t? This cannot be undone.', ['t' => $campaign->localized('title')]) }}')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="rounded-2xl bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 font-bold shadow">
                        🗑️ {{ __('Delete campaign') }}
                    </button>
                </form>
            </div>
        </div>
    @endcan
@endsection
