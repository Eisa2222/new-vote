@extends('layouts.admin')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-800">{{ __('Users') }}</h1>
        <a href="/admin/users/create" class="btn-primary">+ {{ __('New User') }}</a>
    </div>

    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-100 text-slate-600 text-sm">
                <tr>
                    <th class="p-3 text-start">{{ __('Name') }}</th>
                    <th class="p-3 text-start">{{ __('Email') }}</th>
                    <th class="p-3 text-start">{{ __('Roles') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($users as $u)
                    <tr class="hover:bg-slate-50">
                        <td class="p-3 font-medium">{{ $u->name }}</td>
                        <td class="p-3 text-slate-600">{{ $u->email }}</td>
                        <td class="p-3">
                            @foreach($u->roles as $r)
                                <span class="badge-active">{{ $r->name }}</span>
                            @endforeach
                        </td>
                        <td class="p-3 text-end">
                            <a href="/admin/users/{{ $u->id }}/edit" class="btn-ghost">{{ __('Edit') }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
@endsection
