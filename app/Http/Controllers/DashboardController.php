<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Location;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = request()->user();

        if ($user->role === 'admin') {
            $locations = Location::query()
                ->with(['assignedUsers', 'attendanceLogs'])
                ->latest()
                ->get();

            $logs = AttendanceLog::query()
                ->with(['user', 'location'])
                ->latest('marked_at')
                ->latest('id')
                ->get();

            return view('dashboard', [
                'mode' => 'admin',
                'locations' => $locations,
                'users' => User::query()->where('role', 'user')->where('active', true)->orderBy('name')->get(),
                'recentLogs' => $logs->take(5),
                'logs' => $logs,
                'activeUsersCount' => User::query()->where('active', true)->count(),
                'locationsCount' => $locations->count(),
                'todayLogsCount' => $logs->filter(fn ($log) => optional($log->marked_at)->isToday())->count(),
                'assignedLocations' => $locations,
            ]);
        }

        $assignedLocations = $user->assignedLocations()
            ->with(['attendanceLogs'])
            ->latest()
            ->get();

        $logs = $user->attendanceLogs()
            ->with(['location'])
            ->latest('marked_at')
            ->latest('id')
            ->get();

        return view('dashboard', [
            'mode' => 'user',
            'locations' => $assignedLocations,
            'users' => collect(),
            'recentLogs' => $logs->take(5),
            'logs' => $logs,
            'activeUsersCount' => 1,
            'locationsCount' => $assignedLocations->count(),
            'todayLogsCount' => $logs->filter(fn ($log) => optional($log->marked_at)->isToday())->count(),
            'assignedLocations' => $assignedLocations,
        ]);
    }
}
