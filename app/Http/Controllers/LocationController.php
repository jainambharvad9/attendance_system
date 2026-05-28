<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:1', 'max:50000'],
            'is_public' => ['nullable', 'boolean'],
            'assigned_user_ids' => ['nullable', 'array'],
            'assigned_user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $location = DB::transaction(function () use ($request, $data) {
            $location = Location::create([
                'name' => $data['name'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'radius_meters' => $data['radius_meters'],
                'is_public' => $request->boolean('is_public') || empty($data['assigned_user_ids']),
                'created_by_id' => $request->user()->id,
                'active' => true,
            ]);

            if (!$location->is_public) {
                $location->assignedUsers()->sync($data['assigned_user_ids'] ?? []);
            }

            return $location;
        });

        return redirect()->route('dashboard')->with('status', "Location {$location->name} created successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:1', 'max:50000'],
            'is_public' => ['nullable', 'boolean'],
            'assigned_user_ids' => ['nullable', 'array'],
            'assigned_user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $location = Location::findOrFail($id);

        $location->update([
            'name' => $data['name'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'radius_meters' => $data['radius_meters'],
            'is_public' => $request->boolean('is_public') || empty($data['assigned_user_ids']),
        ]);

        if ($location->is_public) {
            $location->assignedUsers()->sync([]);
        } else {
            $location->assignedUsers()->sync($data['assigned_user_ids'] ?? []);
        }

        return redirect()->route('dashboard')->with('status', "Location {$location->name} updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $location = Location::findOrFail($id);
        $name = $location->name;

        $location->assignedUsers()->sync([]);
        $location->delete();

        return redirect()->route('dashboard')->with('status', "Location {$name} deleted successfully.");
    }
}
