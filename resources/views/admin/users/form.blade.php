@extends('layouts.admin')
@section('content')
    <h1 class="text-2xl font-bold mb-6 text-slate-800">{{ $user->exists ? __('Edit User') : __('New User') }}</h1>

    <form method="post" action="{{ $user->exists ? '/admin/users/'.$user->id : '/admin/users' }}"
          class="bg-white rounded-2xl shadow p-6 space-y-5 max-w-2xl">
        @csrf
        @if($user->exists) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Name') }}</label>
            <input name="name" value="{{ old('name', $user->name) }}" required class="w-full border rounded-lg px-3 py-2">
            @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Email') }}</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border rounded-lg px-3 py-2">
            @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ __('Password') }} @if($user->exists)<span class="text-slate-400 text-xs">({{ __('leave empty to keep current') }})</span>@endif
            </label>
            <input type="password" name="password" class="w-full border rounded-lg px-3 py-2" @if(!$user->exists) required @endif>
            @error('password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Roles') }}</label>
            @foreach($roles as $r)
                <label class="flex items-center gap-2 mb-1">
                    <input type="checkbox" name="roles[]" value="{{ $r->name }}"
                           @checked(in_array($r->name, old('roles', $user->roles->pluck('name')->all())))>
                    {{ $r->name }}
                </label>
            @endforeach
        </div>

        <div class="sticky bottom-0 bg-white pt-4 border-t flex gap-2">
            <button class="btn-primary">{{ __('Save') }}</button>
            <a href="/admin/users" class="btn-ghost">{{ __('Cancel') }}</a>
        </div>
    </form>
@endsection
