<?php

namespace Database\Seeders;

use App\Models\AttendanceLog;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@fieldtrack.local'],
            [
                'name' => 'Office Admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'active' => true,
            ]
        );

        $staffA = User::updateOrCreate(
            ['email' => 'rahul@fieldtrack.local'],
            [
                'name' => 'Rahul Sharma',
                'password' => Hash::make('user123'),
                'role' => 'user',
                'active' => true,
            ]
        );

        $staffB = User::updateOrCreate(
            ['email' => 'priya@fieldtrack.local'],
            [
                'name' => 'Priya Patel',
                'password' => Hash::make('user123'),
                'role' => 'user',
                'active' => true,
            ]
        );

        $staffC = User::updateOrCreate(
            ['email' => 'amit@fieldtrack.local'],
            [
                'name' => 'Amit Singh',
                'password' => Hash::make('user123'),
                'role' => 'user',
                'active' => true,
            ]
        );

        $locations = collect([
            ['name' => 'Client HQ Mumbai', 'latitude' => 19.0760, 'longitude' => 72.8777, 'radius_meters' => 200, 'is_public' => false, 'users' => [$staffA, $staffB]],
            ['name' => 'Pune Office Park', 'latitude' => 18.5204, 'longitude' => 73.8567, 'radius_meters' => 150, 'is_public' => false, 'users' => [$staffA, $staffC]],
            ['name' => 'Ahmedabad Site', 'latitude' => 23.0225, 'longitude' => 72.5714, 'radius_meters' => 300, 'is_public' => true, 'users' => []],
            ['name' => 'Surat Warehouse', 'latitude' => 21.1702, 'longitude' => 72.8311, 'radius_meters' => 250, 'is_public' => false, 'users' => [$staffB, $staffC]],
        ])->map(function (array $definition) use ($admin) {
            $location = Location::updateOrCreate(
                ['name' => $definition['name']],
                [
                    'latitude' => $definition['latitude'],
                    'longitude' => $definition['longitude'],
                    'radius_meters' => $definition['radius_meters'],
                    'is_public' => $definition['is_public'],
                    'created_by_id' => $admin->id,
                    'active' => true,
                ]
            );

            if ($location->is_public) {
                $location->assignedUsers()->sync([]);
            } else {
                $location->assignedUsers()->sync(collect($definition['users'])->pluck('id'));
            }

            return $location;
        });

        if (AttendanceLog::query()->count() === 0) {
            $logs = [
                ['user' => $staffA, 'location' => $locations[0], 'offset' => 12, 'accuracy' => 16, 'status' => 'valid'],
                ['user' => $staffB, 'location' => $locations[1], 'offset' => 8, 'accuracy' => 12, 'status' => 'valid'],
                ['user' => $staffC, 'location' => $locations[2], 'offset' => 15, 'accuracy' => 18, 'status' => 'valid'],
                ['user' => $staffA, 'location' => $locations[2], 'offset' => 42, 'accuracy' => 28, 'status' => 'flagged'],
            ];

            foreach ($logs as $index => $entry) {
                AttendanceLog::create([
                    'user_id' => $entry['user']->id,
                    'location_id' => $entry['location']->id,
                    'latitude' => $entry['location']->latitude + ($entry['offset'] / 10000),
                    'longitude' => $entry['location']->longitude + ($entry['offset'] / 10000),
                    'accuracy' => $entry['accuracy'],
                    'distance_meters' => $entry['offset'],
                    'selfie_path' => null,
                    'status' => $entry['status'],
                    'marked_at' => now()->subDays($index),
                ]);
            }
        }
    }
}
