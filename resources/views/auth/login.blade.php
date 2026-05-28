@extends('layouts.app')

@section('title', 'Sign In - FieldTrack')
@section('page', 'login')

@section('content')
    <main class="login-wrap">
        <section class="login-box">
            <a class="login-back" href="{{ route('landing') }}"><i class="bi bi-arrow-left"></i> Back</a>

            <div class="feature-icon" style="margin: 0 0 1rem 0;"><i class="bi bi-person-badge"></i></div>
            <div class="login-title">{{ $role === 'admin' ? 'Admin Sign In' : 'Staff Sign In' }}</div>
            <div class="login-sub">Use your office credentials to continue</div>

            @if ($errors->any())
                <div class="notice-card" style="margin-bottom: 1rem; border-color: rgba(242,92,92,0.35);">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email"
                        value="{{ $role === 'admin' ? 'admin@fieldtrack.local' : 'rahul@fieldtrack.local' }}" required
                        autofocus>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password"
                        value="{{ $role === 'admin' ? 'admin123' : 'user123' }}" required>
                </div>

                <button class="btn btn-primary" type="submit" style="width: 100%;">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Sign In
                </button>
            </form>

            <div class="notice-card" style="margin-top: 1rem;">
                <div class="card-title" style="margin-bottom: 0.45rem;">Demo Credentials</div>
                <div class="mono">admin@fieldtrack.local / admin123</div>
                <div class="mono">staff@fieldtrack.local / staff123</div>
            </div>
        </section>
    </main>
@endsection
