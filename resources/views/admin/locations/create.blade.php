@extends('layouts.app')

@section('title', 'Add Attendance Location - FieldTrack')
@section('page', 'login')

@section('content')
    <main class="login-wrap" style="align-items:flex-start; padding-top: 2rem;">
        <section class="login-box" style="width:min(860px, 100%);">
            <a class="login-back" href="{{ route('dashboard') }}"><i class="bi bi-arrow-left"></i> Back to dashboard</a>

            <div class="feature-icon" style="margin: 0 0 1rem 0;"><i class="bi bi-geo-alt-fill"></i></div>
            <div class="login-title">Add Attendance Location</div>
            <div class="login-sub">Create your Ahmedabad office location for GPS-based attendance</div>

            @if ($errors->any())
                <div class="notice-card" style="margin-bottom: 1rem; border-color: rgba(242,92,92,0.35);">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('locations.store') }}">
                @csrf
                <div class="field">
                    <label for="name">Location Name</label>
                    <input id="name" type="text" name="name" value="FieldTrack Ahmedabad Office" required>
                </div>

                <div class="field">
                    <label for="address">Office Address</label>
                    <textarea id="address" name="address" rows="3" required>Ashwmegh Avenue, nr. Helmet House, Mithakhali, Navrangpura, Ahmedabad, Gujarat 380009</textarea>
                </div>

                <div class="field-grid" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:0.75rem;">
                    <div class="field">
                        <label for="latitude">Latitude</label>
                        <input id="latitude" name="latitude" type="number" step="0.000001" value="23.025300" required>
                    </div>
                    <div class="field">
                        <label for="longitude">Longitude</label>
                        <input id="longitude" name="longitude" type="number" step="0.000001" value="72.559100" required>
                    </div>
                    <div class="field">
                        <label for="radius_meters">Radius (meters)</label>
                        <input id="radius_meters" name="radius_meters" type="number" min="1" value="180"
                            required>
                    </div>
                </div>

                <div class="field" style="display:flex;align-items:center;gap:0.75rem;">
                    <input id="is_public" name="is_public" type="checkbox" value="1" checked style="width:auto;">
                    <label for="is_public" style="margin:0;text-transform:none;letter-spacing:0;">Allow all active staff to
                        use this location</label>
                </div>

                <div class="field">
                    <label for="assigned_user_ids">Assign Staff</label>
                    <select id="assigned_user_ids" name="assigned_user_ids[]" multiple size="5">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <div class="field-hint" style="margin-top:0.35rem;">If this is public, selection is optional.</div>
                </div>

                <div class="btn-stack" style="justify-content:flex-end;">
                    <a class="btn btn-ghost" href="{{ route('dashboard') }}"><i class="bi bi-x-circle"></i> Cancel</a>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Save
                        Location</button>
                </div>
            </form>
        </section>
    </main>
@endsection
