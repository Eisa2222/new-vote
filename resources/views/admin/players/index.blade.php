@extends('layouts.admin')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-800">{{ __('Players') }}</h1>
        <a href="/admin/players/create" class="btn-primary">+ {{ __('New Player') }}</a>
    </div>

    <form method="get" class="mb-4 flex gap-2 flex-wrap">
        <input name="q" value="{{ request('q') }}" placeholder="{{ __('Search') }}..."
               class="border rounded-lg px-3 py-2 flex-1 min-w-[200px]">
        <select name="club_id" class="border rounded-lg px-3 py-2">
            <option value="">{{ __('All clubs') }}</option>
            @foreach($clubs as $c)
                <option value="{{ $c->id }}" @selected(request('club_id') == $c->id)>{{ $c->localized('name') }}</option>
            @endforeach
        </select>
        <select name="position" class="border rounded-lg px-3 py-2">
            <option value="">{{ __('All positions') }}</option>
            @foreach($positions as $p)
                <option value="{{ $p->value }}" @selected(request('position') === $p->value)>{{ $p->label() }}</option>
            @endforeach
        </select>
        <button class="btn-primary">{{ __('Filter') }}</button>
    </form>

    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-100 text-slate-600 text-sm">
                <tr>
                    <th class="p-3 text-start">{{ __('Photo') }}</th>
                    <th class="p-3 text-start">{{ __('Name') }}</th>
                    <th class="p-3 text-start">{{ __('Club') }}</th>
                    <th class="p-3 text-start">{{ __('Position') }}</th>
                    <th class="p-3 text-start">#</th>
                    <th class="p-3 text-start">{{ __('Status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($players as $p)
                    <tr class="hover:bg-slate-50">
                        <td class="p-3">
                            @if($p->photo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($p->photo_path) }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-400">{{ mb_substr($p->name_en,0,1) }}</div>
                            @endif
                        </td>
                        <td class="p-3">
                            <div class="font-medium">{{ $p->localized('name') }}</div>
                            @if($p->is_captain) <span class="text-amber-500 text-xs">★ {{ __('Captain') }}</span>@endif
                        </td>
                        <td class="p-3 text-slate-600">{{ $p->club?->localized('name') }}</td>
                        <td class="p-3"><span class="badge-active">{{ $p->position?->label() }}</span></td>
                        <td class="p-3 text-slate-600">{{ $p->jersey_number }}</td>
                        <td class="p-3"><span class="badge-{{ $p->status->value }}">{{ $p->status->label() }}</span></td>
                        <td class="p-3 text-end">
                            <a href="/admin/players/{{ $p->id }}/edit" class="btn-ghost">{{ __('Edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-12 text-center text-slate-400">{{ __('No players yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $players->links() }}</div>
@endsection
