@extends('layouts.app')

@section('title', 'Staff Records - FieldTrack')
@section('page', 'dashboard')

@section('content')
    <div class="screen-shell" data-mode="admin">
        <header class="topbar">
            <div class="topbar-logo"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="topbar-title">FieldTrack</div>
                <div class="topbar-role">Admin Staff Records</div>
            </div>
            <div class="topbar-right">
                <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <a class="btn btn-ghost btn-sm" href="{{ route('dashboard') }}"><i class="bi bi-grid"></i> Dashboard</a>
            </div>
        </header>

        <main class="main-content">
            <div class="section-header" style="margin: 1rem 0 1.1rem;">
                <div>
                    <div class="section-title">Staff Record List</div>
                    <div class="help-text">Shows the staff ID and login password stored for each office user.</div>
                </div>
                <div class="btn-stack">
                    <a class="btn btn-ghost btn-sm" href="{{ route('admin.staff.create') }}">
                        <i class="bi bi-person-plus"></i> Register Staff
                    </a>
                </div>
            </div>

            <section class="card" style="overflow-x:auto;">
                <div class="card-title">Staff Directory</div>
                <table class="log-table">
                    <thead>
                        <tr>
                            <th>Staff ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($staffMembers as $staff)
                            <tr>
                                <td class="mono">{{ $staff->id }}</td>
                                <td>{{ $staff->name }}</td>
                                <td>{{ $staff->email }}</td>
                                <td class="mono">Hidden</td>
                                <td>
                                    <span class="badge {{ $staff->active ? 'badge-success' : 'badge-warn' }}">
                                        {{ $staff->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <a class="btn btn-ghost btn-sm" href="{{ route('admin.staff.password.edit', $staff) }}">
                                        <i class="bi bi-shield-lock"></i> Reset Password
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-state">No staff records available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </div>
@endsection