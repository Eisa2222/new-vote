@extends('layouts.admin')
@section('content')
    <h1 class="text-2xl font-bold mb-6 text-slate-800">{{ __('Dashboard') }}</h1>

    <div class="grid grid-cols-4 gap-4 mb-8">
        @php
            $stats = [
                ['label' => __('Clubs'),     'value' => \App\Modules\Clubs\Models\Club::count(),     'color' => 'emerald'],
                ['label' => __('Players'),   'value' => \App\Modules\Players\Models\Player::count(), 'color' => 'blue'],
                ['label' => __('Campaigns'), 'value' => \App\Modules\Campaigns\Models\Campaign::count(), 'color' => 'purple'],
                ['label' => __('Votes'),     'value' => \App\Modules\Voting\Models\Vote::count(),  'color' => 'rose'],
            ];
        @endphp
        @foreach($stats as $s)
            <div class="bg-white rounded-2xl shadow p-5">
                <div class="text-slate-500 text-sm">{{ $s['label'] }}</div>
                <div class="text-3xl font-bold text-{{ $s['color'] }}-600 mt-1">{{ $s['value'] }}</div>
            </div>
        @endforeach
    </div>
@endsection
