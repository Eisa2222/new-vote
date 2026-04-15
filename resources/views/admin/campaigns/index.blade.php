@extends('layouts.admin')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-800">{{ __('Campaigns') }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($campaigns as $c)
            <a href="/admin/campaigns/{{ $c->id }}" class="block bg-white rounded-2xl shadow p-5 hover:shadow-md transition">
                <div class="flex items-start justify-between mb-2">
                    <h3 class="font-semibold text-slate-800">{{ $c->localized('title') }}</h3>
                    <span class="badge-{{ $c->status->value }}">{{ $c->status->value }}</span>
                </div>
                <div class="text-sm text-slate-500 mb-3">{{ $c->type->value }}</div>
                <div class="text-xs text-slate-500">
                    {{ $c->start_at->format('Y-m-d H:i') }} → {{ $c->end_at->format('Y-m-d H:i') }}
                </div>
                <div class="mt-3 pt-3 border-t flex justify-between text-sm">
                    <span class="text-slate-600">{{ __('Votes') }}: <strong>{{ $c->votes_count }}</strong></span>
                    @if($c->max_voters)
                        <span class="text-slate-500">{{ __('Max') }}: {{ $c->max_voters }}</span>
                    @endif
                </div>
            </a>
        @empty
            <div class="col-span-2 bg-white rounded-2xl shadow p-12 text-center text-slate-400">
                {{ __('No campaigns yet.') }}
            </div>
        @endforelse
    </div>
    <div class="mt-4">{{ $campaigns->links() }}</div>
@endsection
