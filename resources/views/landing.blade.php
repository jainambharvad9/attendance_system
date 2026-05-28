@extends('layouts.app')

@section('title', 'FieldTrack - Attendance System')
@section('page', 'landing')

@section('content')
    <main class="hero-shell">
        <section class="hero-card">
            <div class="hero-logo"><i class="bi bi-geo-alt-fill"></i></div>
            <h1 class="hero-title">FieldTrack</h1>
            <p class="hero-subtitle">GPS verified attendance for office and field teams</p>

            <div class="hero-grid">
                <a class="role-card" href="{{ route('login', ['role' => 'admin']) }}">
                    <div class="role-icon"><i class="bi bi-shield-lock"></i></div>
                    <div class="role-label">Admin</div>
                    <div class="role-desc">Manage locations, users, maps, and attendance records</div>
                </a>
                <a class="role-card" href="{{ route('login', ['role' => 'user']) }}">
                    <div class="role-icon"><i class="bi bi-phone"></i></div>
                    <div class="role-label">Field Staff</div>
                    <div class="role-desc">Mark attendance with live GPS and selfie capture</div>
                </a>
            </div>

            <p class="hero-note" style="margin-top: 1.5rem;">Demo accounts are available after login</p>
            <div class="hero-actions">
                <a class="btn btn-ghost" href="{{ route('login', ['role' => 'admin']) }}"><i
                        class="bi bi-box-arrow-in-right"></i> Admin Sign In</a>
                <a class="btn btn-ghost" href="{{ route('login', ['role' => 'user']) }}"><i
                        class="bi bi-box-arrow-in-right"></i> Staff Sign In</a>
            </div>
        </section>
    </main>
@endsection
