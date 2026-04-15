@extends('layouts.admin')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-800">{{ __('Clubs') }}</h1>
        <a href="/admin/clubs/create" class="btn-primary">+ {{ __('New Club') }}</a>
    </div>

    <form method="get" class="mb-4 flex gap-2">
        <input name="q" value="{{ request('q') }}" placeholder="{{ __('Search') }}..."
               class="border rounded-lg px-3 py-2 flex-1">
        <select name="status" class="border rounded-lg px-3 py-2">
            <option value="">{{ __('All statuses') }}</option>
            <option value="active"   @selected(request('status') === 'active')>{{ __('Active') }}</option>
            <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
        </select>
        <button class="btn-primary">{{ __('Filter') }}</button>
    </form>

    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-100 text-slate-600 text-sm">
                <tr>
                    <th class="p-3 text-start">{{ __('Logo') }}</th>
                    <th class="p-3 text-start">{{ __('Name') }}</th>
                    <th class="p-3 text-start">{{ __('Short') }}</th>
                    <th class="p-3 text-start">{{ __('Sports') }}</th>
                    <th class="p-3 text-start">{{ __('Status') }}</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($clubs as $club)
                    <tr class="hover:bg-slate-50">
                        <td class="p-3">
                            @if($club->logo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($club->logo_path) }}"
                                     class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-slate-200"></div>
                            @endif
                        </td>
                        <td class="p-3 font-medium">{{ $club->localized('name') }}</td>
                        <td class="p-3 text-slate-500">{{ $club->short_name }}</td>
                        <td class="p-3 text-slate-500">
                            {{ $club->sports->pluck('name_'.app()->getLocale())->join(' · ') }}
                        </td>
                        <td class="p-3">
                            <span class="badge-{{ $club->status->value }}">{{ $club->status->label() }}</span>
                        </td>
                        <td class="p-3 text-end">
                            <a href="/admin/clubs/{{ $club->id }}/edit" class="btn-ghost">{{ __('Edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-12 text-center text-slate-400">
                        {{ __('No clubs yet. Create your first club.') }}
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $clubs->links() }}</div>
@endsection
