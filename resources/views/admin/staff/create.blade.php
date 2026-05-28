@extends('layouts.app')

@section('title', 'Register Staff - FieldTrack')
@section('page', 'login')

@section('content')
    <main class="login-wrap" style="align-items:flex-start; padding-top: 2rem;">
        <section class="login-box" style="width:min(720px, 100%);">
            <a class="login-back" href="{{ route('dashboard') }}"><i class="bi bi-arrow-left"></i> Back to dashboard</a>

            <div class="feature-icon" style="margin: 0 0 1rem 0;"><i class="bi bi-person-plus"></i></div>
            <div class="login-title">Register Staff</div>
            <div class="login-sub">Create a new office user for attendance marking</div>

            @if ($errors->any())
                <div class="notice-card" style="margin-bottom: 1rem; border-color: rgba(242,92,92,0.35);">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.staff.store') }}">
                @csrf
                <div class="field">
                    <label for="name">Staff Name</label>
                    <input id="name" type="text" name="name" placeholder="Ahmedabad Staff" required>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" placeholder="staff@fieldtrack.local" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" placeholder="Minimum 6 characters" required>
                </div>

                <div class="field" style="display:flex;align-items:center;gap:0.75rem;">
                    <input id="active" name="active" type="checkbox" value="1" checked style="width:auto;">
                    <label for="active" style="margin:0;text-transform:none;letter-spacing:0;">Active staff
                        account</label>
                </div>

                <div class="btn-stack" style="justify-content:flex-end;">
                    <a class="btn btn-ghost" href="{{ route('dashboard') }}"><i class="bi bi-x-circle"></i> Cancel</a>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Create Staff</button>
                </div>
            </form>
        </section>
    </main>
@endsection
