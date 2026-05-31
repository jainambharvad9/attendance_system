<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminStaffController extends Controller
{
    public function index(): View
    {
        $staffMembers = User::query()
            ->where('role', 'user')
            ->orderBy('name')
            ->get();

        return view('admin.staff.index', [
            'staffMembers' => $staffMembers,
        ]);
    }

    public function editPassword(User $staff): View
    {
        abort_unless($staff->role === 'user', 404);

        return view('admin.staff.password', [
            'staff' => $staff,
        ]);
    }

    public function updatePassword(Request $request, User $staff): RedirectResponse
    {
        abort_unless($staff->role === 'user', 404);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $staff->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        return redirect()
            ->route('admin.staff.index')
            ->with('status', "Password reset for {$staff->name} successfully.");
    }

    public function create(): View
    {
        return view('admin.staff.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'active' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'active' => $request->boolean('active', true),
        ]);

        return redirect()->route('dashboard')->with('status', 'Staff member created successfully.');
    }
}
