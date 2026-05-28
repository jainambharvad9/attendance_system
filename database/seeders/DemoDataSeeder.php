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
        AttendanceLog::query()->delete();
        Location::query()->delete();
        User::query()->delete();

        $admin = User::create([
            'name' => 'Office Admin',
            'email' => 'admin@fieldtrack.local',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'active' => true,
        ]);

        $staff = User::create([
            'name' => 'Ahmedabad Staff',
            'email' => 'staff@fieldtrack.local',
            'password' => Hash::make('staff123'),
            'role' => 'user',
            'active' => true,
        ]);

        $location = Location::create([
            'name' => 'FieldTrack Ahmedabad Office',
            'address' => 'Ashwmegh Avenue, nr. Helmet House, Mithakhali, Navrangpura, Ahmedabad, Gujarat 380009',
            'latitude' => 23.0253,
            'longitude' => 72.5591,
            'radius_meters' => 180,
            'is_public' => true,
            'created_by_id' => $admin->id,
            'active' => true,
        ]);

        $location->assignedUsers()->sync([]);

        if (AttendanceLog::query()->count() === 0) {
            AttendanceLog::create([
                'user_id' => $staff->id,
                'location_id' => $location->id,
                'latitude' => $location->latitude + 0.00008,
                'longitude' => $location->longitude + 0.00007,
                'accuracy' => 14,
                'distance_meters' => 12,
                'selfie_path' => null,
                'status' => 'valid',
                'marked_at' => now()->subHour(),
            ]);
        }
    }
}
