@extends('layouts.app')

@section('title', 'FieldTrack Dashboard')
@section('page', 'dashboard')

@php
    $locationPayload = $locations->map(
        fn($location) => [
            'id' => $location->id,
            'name' => $location->name,
            'address' => $location->address,
            'latitude' => (float) $location->latitude,
            'longitude' => (float) $location->longitude,
            'radius_meters' => (int) $location->radius_meters,
            'is_public' => (bool) $location->is_public,
            'assigned_user_ids' => $location->assignedUsers->pluck('id')->values(),
            'assigned_label' => $location->is_public
                ? 'All Staff'
                : $location->assignedUsers->pluck('name')->implode(', '),
        ],
    );

    $logPayload = $logs->map(
        fn($log) => [
            'id' => $log->id,
            'user_name' => $log->user->name,
            'location_name' => $log->location->name,
            'marked_at' => optional($log->marked_at)->format('M d, Y h:i A'),
            'status' => $log->status,
            'latitude' => (float) $log->latitude,
            'longitude' => (float) $log->longitude,
            'accuracy' => (float) ($log->accuracy ?? 0),
            'distance_meters' => (float) ($log->distance_meters ?? 0),
            'selfie_url' => $log->selfie_url,
        ],
    );

    $userPayload = $users->map(
        fn($user) => [
            'id' => $user->id,
            'name' => $user->name,
        ],
    );
@endphp

@section('content')
    <div class="screen-shell" data-mode="{{ $mode }}">
        <header class="topbar">
            <div class="topbar-logo"><i class="bi bi-geo-alt-fill"></i></div>
            <div>
                <div class="topbar-title">FieldTrack</div>
                <div class="topbar-role">{{ $mode === 'admin' ? 'Admin Panel' : 'Field Staff Panel' }}</div>
            </div>
            <div class="topbar-right">
                <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-ghost btn-sm" type="submit"><i class="bi bi-box-arrow-right"></i> Sign
                        Out</button>
                </form>
            </div>
        </header>

        <main class="main-content">
            @if (session('status'))
                <div class="notice-card">
                    {{ session('status') }}
                </div>
            @endif

            @if ($mode === 'admin')
                <nav class="nav-tabs">
                    <div class="nav-tab active" data-admin-tab="dashboard">Dashboard</div>
                    <div class="nav-tab" data-admin-tab="locations">Locations</div>
                    <div class="nav-tab" data-admin-tab="logs">Attendance</div>
                    <div class="nav-tab" data-admin-tab="map">Map View</div>
                </nav>

                <div class="section-header" style="margin: 1rem 0 1.1rem;">
                    <div>
                        <div class="section-title">Admin Setup</div>
                        <div class="help-text">Manage your Ahmedabad office location and register new staff from here.</div>
                    </div>
                    <div class="btn-stack">
                        <a class="btn btn-primary btn-sm" href="{{ route('locations.create') }}">
                            <i class="bi bi-geo-alt-fill"></i> Add Attendance Location
                        </a>
                        <a class="btn btn-ghost btn-sm" href="{{ route('admin.staff.index') }}">
                            <i class="bi bi-people"></i> Staff Records
                        </a>
                        <a class="btn btn-ghost btn-sm" href="{{ route('admin.staff.create') }}">
                            <i class="bi bi-person-plus"></i> Register Staff
                        </a>
                    </div>
                </div>

                <section data-admin-pane="dashboard">
                    <div class="stat-row">
                        <div class="stat-box">
                            <div class="stat-num" style="color: var(--accent);">{{ $activeUsersCount }}</div>
                            <div class="stat-lbl">Active Users</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-num" style="color: var(--accent2);">{{ $locationsCount }}</div>
                            <div class="stat-lbl">Locations</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-num" style="color: var(--success);">{{ $todayLogsCount }}</div>
                            <div class="stat-lbl">Today's Logs</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-num" style="color: var(--warn);">
                                {{ $logs->where('status', 'flagged')->count() }}</div>
                            <div class="stat-lbl">Flagged</div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-title">Recent Attendance</div>
                        <table class="log-table">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentLogs as $log)
                                    <tr>
                                        <td>
                                            <div class="log-thumb">
                                                @if ($log->selfie_path)
                                                    <img src="{{ $log->selfie_url }}" alt="Selfie">
                                                @else
                                                    <i class="bi bi-person-circle"></i>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $log->user->name }}</td>
                                        <td>{{ $log->location->name }}</td>
                                        <td class="mono">{{ optional($log->marked_at)->format('M d, h:i A') }}</td>
                                        <td>
                                            <span
                                                class="badge {{ $log->status === 'valid' ? 'badge-success' : 'badge-warn' }}">{{ ucfirst($log->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="empty-state">No attendance records yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section data-admin-pane="locations" class="hidden">
                    <div class="section-header">
                        <div>
                            <div class="section-title">Client Locations</div>
                            <div class="help-text">Manage office and client sites from a single panel.</div>
                        </div>
                        <button class="btn btn-primary btn-sm" type="button" onclick="openLocationModal()"><i
                                class="bi bi-plus-lg"></i> Add Location</button>
                    </div>

                    <div class="card">
                        @forelse ($locations as $location)
                            <div class="loc-item">
                                <div class="loc-dot"><i class="bi bi-geo-alt-fill"></i></div>
                                <div class="loc-info">
                                    <div class="loc-name">{{ $location->name }}</div>
                                    <div class="location-meta">{{ $location->address }}</div>
                                    <div class="loc-coords">{{ number_format($location->latitude, 4) }},
                                        {{ number_format($location->longitude, 4) }}</div>
                                    <div class="loc-radius">Radius: {{ $location->radius_meters }} m</div>
                                    <div class="location-meta">
                                        {{ $location->is_public ? 'Assigned to all staff' : $location->assignedUsers->pluck('name')->implode(', ') }}
                                    </div>
                                </div>
                                <div class="loc-actions">
                                    <button class="btn btn-ghost btn-sm" type="button"
                                        onclick="openLocationModal(@js($locationPayload->firstWhere('id', $location->id)))">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <form method="POST" action="{{ route('locations.destroy', $location) }}"
                                        onsubmit="return confirm('Delete this location?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" type="submit"><i class="bi bi-trash3"></i>
                                            Delete</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">No client locations have been added.</div>
                        @endforelse
                    </div>
                </section>

                <section data-admin-pane="logs" class="hidden">
                    <div class="section-header">
                        <div>
                            <div class="section-title">All Attendance Logs</div>
                            <div class="help-text">Saved in MySQL with live GPS coordinates and selfie evidence.</div>
                        </div>
                        <div class="mono">{{ $logs->count() }} records</div>
                    </div>

                    <div class="card" style="overflow-x: auto;">
                        <table class="log-table">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Time</th>
                                    <th>GPS</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td>
                                            <div class="log-thumb">
                                                @if ($log->selfie_path)
                                                    <img src="{{ $log->selfie_url }}" alt="Selfie">
                                                @else
                                                    <i class="bi bi-person-circle"></i>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $log->user->name }}</td>
                                        <td>{{ $log->location->name }}</td>
                                        <td class="mono">{{ optional($log->marked_at)->format('M d, Y h:i A') }}</td>
                                        <td class="mono">{{ number_format($log->latitude, 5) }},
                                            {{ number_format($log->longitude, 5) }}</td>
                                        <td><span
                                                class="badge {{ $log->status === 'valid' ? 'badge-success' : 'badge-warn' }}">{{ ucfirst($log->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="empty-state">No attendance logs available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section data-admin-pane="map" class="hidden">
                    <div class="section-title" style="margin-bottom: 0.85rem;">Location Map</div>
                    <div id="admin-map-canvas" class="map-canvas">
                        <div class="map-legend">
                            <div class="map-legend-item"><span class="map-legend-dot"
                                    style="background: var(--accent2);"></span> Client Site</div>
                            <div class="map-legend-item"><span class="map-legend-dot"
                                    style="background: var(--warn);"></span> Attendance</div>
                        </div>
                    </div>
                </section>

                <div class="modal-overlay hidden" id="location-modal">
                    <div class="modal-sheet">
                        <div class="modal-title">
                            <span id="location-modal-title">Add Location</span>
                            <span class="modal-close" onclick="closeLocationModal()"><i class="bi bi-x-lg"></i></span>
                        </div>

                        <form id="location-form" method="POST" action="{{ route('locations.store') }}">
                            @csrf
                            <div class="field">
                                <label for="location-name">Location Name</label>
                                <input id="location-name" name="name" type="text"
                                    placeholder="FieldTrack Ahmedabad Office" required>
                            </div>

                            <div class="field">
                                <label for="location-address">Office Address</label>
                                <textarea id="location-address" name="address" rows="3"
                                    placeholder="Ashwmegh Avenue, nr. Helmet House, Mithakhali, Navrangpura, Ahmedabad, Gujarat 380009" required>
                                </textarea>
                            </div>
                            <div class="field-grid"
                                style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:0.75rem;">
                                <div class="field">
                                    <label for="location-latitude">Latitude</label>
                                    <input id="location-latitude" name="latitude" type="number" step="0.000001"
                                        placeholder="23.025300" required>
                                </div>
                                <div class="field">
                                    <label for="location-longitude">Longitude</label>
                                    <input id="location-longitude" name="longitude" type="number" step="0.000001"
                                        placeholder="72.559100" required>
                                </div>
                                <div class="field">
                                    <label for="location-radius">Radius (m)</label>
                                    <input id="location-radius" name="radius_meters" type="number" min="1"
                                        value="200" required>
                                </div>
                            </div>

                            <div class="field" style="display:flex;align-items:center;gap:0.75rem;">
                                <input id="location-is-public" name="is_public" type="checkbox" value="1"
                                    style="width:auto;">
                                <label for="location-is-public"
                                    style="margin:0;text-transform:none;letter-spacing:0;">Assign to all active
                                    staff</label>
                            </div>

                            <div class="field">
                                <label for="location-users">Assigned Staff</label>
                                <select id="location-users" name="assigned_user_ids[]" multiple size="4">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <div class="field-hint" style="margin-top:0.35rem;">Hold Ctrl or Cmd to select more than
                                    one staff member.</div>
                            </div>

                            <div class="btn-stack" style="justify-content:flex-end;">
                                <button class="btn btn-ghost" type="button" onclick="closeLocationModal()"><i
                                        class="bi bi-x-circle"></i> Cancel</button>
                                <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Save
                                    Location</button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <nav class="bottomnav" style="margin-bottom: 1rem;">
                    <div class="bottomnav-item active" data-user-tab="mark">
                        <div class="nav-icon"><i class="bi bi-geo-alt-fill"></i></div>Mark
                    </div>
                    <div class="bottomnav-item" data-user-tab="logs">
                        <div class="nav-icon"><i class="bi bi-journal-text"></i></div>My Logs
                    </div>
                </nav>

                <section id="upanel-mark">
                    <div class="section-title" style="margin-bottom: 0.85rem;">Mark Attendance</div>

                    <div id="step-choose">
                        <div class="card">
                            <div class="card-title">Step 1 - Select Client Site</div>
                            @forelse ($locations as $location)
                                <div class="assigned-card" data-location-card data-location-id="{{ $location->id }}">
                                    <div class="assigned-icon"><i class="bi bi-building"></i></div>
                                    <div class="assigned-info">
                                        <div class="assigned-name">{{ $location->name }}</div>
                                        <div class="assigned-dist">Radius {{ $location->radius_meters }} m ·
                                            {{ number_format($location->latitude, 4) }},
                                            {{ number_format($location->longitude, 4) }}</div>
                                    </div>
                                    <i class="bi bi-chevron-right"></i>
                                </div>
                            @empty
                                <div class="empty-state">No assigned locations available.</div>
                            @endforelse
                        </div>
                    </div>

                    <div id="step-gps" class="hidden">
                        <div class="card">
                            <div class="card-title">Step 2 - GPS Verification</div>
                            <div class="gps-status-ring detecting" id="gps-ring"><i class="bi bi-geo-alt"></i></div>
                            <div class="gps-label" id="gps-message">Detecting your location</div>
                            <div class="gps-coords" id="gps-coords">Waiting for GPS...</div>
                            <div id="gps-result" class="gps-result hidden" style="margin-bottom: 1rem;">
                                <span class="badge" id="distance-badge"></span>
                            </div>
                            <div class="btn-stack">
                                <button class="btn btn-primary" id="detect-button" type="button"
                                    onclick="detectGPS()"><i class="bi bi-crosshair"></i> Detect My Location</button>
                                <button class="btn btn-ghost" type="button" onclick="backToChoose()"><i
                                        class="bi bi-arrow-left"></i> Back</button>
                            </div>
                        </div>
                    </div>

                    <div id="step-selfie" class="hidden">
                        <div class="card">
                            <div class="card-title">Step 3 - Capture Selfie</div>

                            <form id="attendance-form" method="POST" action="{{ route('attendance.store') }}"
                                enctype="multipart/form-data" onsubmit="return submitAttendance()">
                                @csrf
                                <input type="hidden" id="attendance-location-id" name="location_id">
                                <input type="hidden" id="attendance-latitude" name="latitude">
                                <input type="hidden" id="attendance-longitude" name="longitude">
                                <input type="hidden" id="attendance-accuracy" name="accuracy">
                                <input type="hidden" id="attendance-distance" name="distance_meters">
                                <input type="file" id="attendance-selfie" name="selfie" accept="image/*"
                                    capture="user" class="hidden">

                                <div id="camera-stage" class="camera-stage">
                                    <video id="camera-video" autoplay playsinline muted></video>
                                    <canvas id="camera-canvas"></canvas>
                                    <div class="camera-overlay">
                                        <div class="camera-corner tl"></div>
                                        <div class="camera-corner tr"></div>
                                        <div class="camera-corner bl"></div>
                                        <div class="camera-corner br"></div>
                                    </div>
                                </div>

                                <img id="selfie-preview" class="selfie-preview" alt="Selfie preview">

                                <div class="btn-stack" id="camera-actions">
                                    <button class="btn btn-primary" id="open-camera-button" type="button"
                                        onclick="openCamera()"><i class="bi bi-camera-fill"></i> Open Camera</button>
                                    <button class="btn btn-success hidden" id="capture-button" type="button"
                                        onclick="captureSelfie()"><i class="bi bi-camera-reels"></i> Capture
                                        Selfie</button>
                                    <button class="btn btn-ghost hidden" id="retake-button" type="button"
                                        onclick="retakePhoto()"><i class="bi bi-arrow-counterclockwise"></i>
                                        Retake</button>
                                </div>

                                <div class="btn-stack" style="margin-top: 0.85rem;">
                                    <button class="btn btn-warn hidden" id="attendance-submit" type="submit"><i
                                            class="bi bi-check2-circle"></i> Submit Attendance</button>
                                    <button class="btn btn-ghost" type="button" onclick="backToGps()"><i
                                            class="bi bi-arrow-left"></i> Back</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                <section id="upanel-logs" class="hidden">
                    <div class="section-title" style="margin-bottom: 0.85rem;">My Attendance History</div>
                    <div class="card" style="overflow-x: auto;">
                        <table class="log-table">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Site</th>
                                    <th>Date / Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td>
                                            <div class="log-thumb">
                                                @if ($log->selfie_path)
                                                    <img src="{{ $log->selfie_url }}" alt="Selfie">
                                                @else
                                                    <i class="bi bi-person-circle"></i>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $log->location->name }}</td>
                                        <td class="mono">{{ optional($log->marked_at)->format('M d, Y h:i A') }}</td>
                                        <td><span
                                                class="badge {{ $log->status === 'valid' ? 'badge-success' : 'badge-warn' }}">{{ ucfirst($log->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="empty-state">No attendance history yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <div class="card" style="margin-top: 1rem;">
                    <div class="card-title">Assigned Location Map</div>
                    <div id="user-map-canvas" class="map-canvas">
                        <div class="map-legend">
                            <div class="map-legend-item"><span class="map-legend-dot"
                                    style="background: var(--accent2);"></span> Site</div>
                            <div class="map-legend-item"><span class="map-legend-dot"
                                    style="background: var(--warn);"></span> Check-in</div>
                        </div>
                    </div>
                </div>
            @endif
        </main>
    </div>

    @php
        $jsRoutes = [
            'locationStore' => route('locations.store'),
            'locationBase' => url('/locations'),
            'attendanceStore' => route('attendance.store'),
        ];
    @endphp

    <script>
        window.FIELDTRACK = {
            mode: @json($mode),
            routes: {!! json_encode($jsRoutes) !!},
            locations: @json($locationPayload),
            logs: @json($logPayload),
            users: @json($userPayload),
        };
    </script>
@endsection
