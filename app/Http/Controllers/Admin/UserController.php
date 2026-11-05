<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Every account here is a full admin. The screen exists so a second admin can
 * be added without touching the database, and so the last one cannot be removed.
 */
class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', ['user' => new User]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Akun dibuat.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->fill($data)->save();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Perubahan disimpan.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        // Deleting the last account locks everyone out of the CMS.
        if (User::query()->count() <= 1) {
            return back()->withErrors(['user' => 'Akun admin terakhir tidak bisa dihapus.']);
        }

        $self = $user->is($request->user());

        $user->delete();

        if ($self) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')->with('status', 'Akun Anda dihapus.');
        }

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Akun dihapus.');
    }
}
