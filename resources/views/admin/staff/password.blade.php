@extends('layouts.app')

@section('title', 'Reset Staff Password - FieldTrack')
@section('page', 'dashboard')

@section('content')
    <div class="screen-shell" data-mode="admin">
        <header class="topbar">
            <div class="topbar-logo"><i class="bi bi-shield-lock-fill"></i></div>
            <div>
                <div class="topbar-title">FieldTrack</div>
                <div class="topbar-role">Reset Staff Password</div>
            </div>
            <div class="topbar-right">
                <a class="btn btn-ghost btn-sm" href="{{ route('admin.staff.index') }}"><i class="bi bi-arrow-left"></i> Back
                    to Staff</a>
            </div>
        </header>

        <main class="main-content">
            <div class="section-header" style="margin: 1rem 0 1.1rem;">
                <div>
                    <div class="section-title">Reset Password</div>
                    <div class="help-text">Create a new login password for {{ $staff->name }} without exposing the old one.
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="notice-card" style="margin-bottom: 1rem; border-color: rgba(242,92,92,0.35);">
                    {{ $errors->first() }}
                </div>
            @endif

            <section class="card" style="max-width: 720px;">
                <div class="card-title">{{ $staff->name }}</div>
                <div class="help-text" style="margin-bottom: 1rem;">
                    Staff ID: <span class="mono">{{ $staff->id }}</span> · Email: {{ $staff->email }}
                </div>

                <form method="POST" action="{{ route('admin.staff.password.update', $staff) }}">
                    @csrf
                    @method('PUT')

                    <div class="field">
                        <label for="password">New Password</label>
                        <input id="password" type="password" name="password" minlength="6" required
                            placeholder="Enter new password">
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" minlength="6"
                            required placeholder="Repeat new password">
                    </div>

                    <div class="btn-stack" style="justify-content:flex-end;">
                        <a class="btn btn-ghost" href="{{ route('admin.staff.index') }}"><i class="bi bi-x-circle"></i>
                            Cancel</a>
                        <button class="btn btn-primary" type="submit"><i class="bi bi-arrow-repeat"></i> Reset
                            Password</button>
                    </div>
                </form>
            </section>
        </main>
    </div>
@endsection
