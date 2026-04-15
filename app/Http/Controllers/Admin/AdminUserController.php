<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

final class AdminUserController extends Controller
{
    private function authorizeManage(): void
    {
        abort_unless(auth()->user()?->can('users.manage'), 403);
    }

    public function index(): View
    {
        $this->authorizeManage();
        $users = User::with('roles')->orderByDesc('id')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorizeManage();
        return view('admin.users.form', ['user' => new User(), 'roles' => Role::all()]);
    }

    public function store(Request $r): RedirectResponse
    {
        $this->authorizeManage();
        $data = $r->validate([
            'name'     => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'roles'    => ['array'],
            'roles.*'  => ['string', Rule::exists('roles', 'name')],
        ]);

        $user = User::create([
            'name' => $data['name'], 'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->syncRoles($data['roles'] ?? []);

        return redirect('/admin/users')->with('success', __('User created.'));
    }

    public function edit(User $user): View
    {
        $this->authorizeManage();
        return view('admin.users.form', ['user' => $user->load('roles'), 'roles' => Role::all()]);
    }

    public function update(Request $r, User $user): RedirectResponse
    {
        $this->authorizeManage();
        $data = $r->validate([
            'name'     => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'roles'    => ['array'],
            'roles.*'  => ['string', Rule::exists('roles', 'name')],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        $user->syncRoles($data['roles'] ?? []);

        return redirect('/admin/users')->with('success', __('User updated.'));
    }
}
