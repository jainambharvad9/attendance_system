<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'location_id' => ['required', 'integer', Rule::exists('locations', 'id')],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['required', 'numeric', 'min:0'],
            'selfie' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $location = Location::findOrFail($data['location_id']);
        $user = $request->user();

        if ($user->role !== 'admin' && !$location->is_public && !$location->assignedUsers()->whereKey($user->id)->exists()) {
            abort(403, 'You are not assigned to this location.');
        }

        $distance = $this->haversineDistance(
            (float) $data['latitude'],
            (float) $data['longitude'],
            (float) $location->latitude,
            (float) $location->longitude,
        );

        $status = $distance <= $location->radius_meters && (float) $data['accuracy'] <= 100 ? 'valid' : 'flagged';

        $selfiePath = $request->file('selfie')->store('attendance-selfies/'.$user->id, 'public');

        AttendanceLog::create([
            'user_id' => $user->id,
            'location_id' => $location->id,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'accuracy' => $data['accuracy'],
            'distance_meters' => $distance,
            'selfie_path' => $selfiePath,
            'status' => $status,
            'marked_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('status', $status === 'valid' ? 'Attendance marked successfully.' : 'Attendance marked, but it needs review.');
    }

    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
