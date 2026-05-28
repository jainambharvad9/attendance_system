<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius_meters',
        'is_public',
        'created_by_id',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'radius_meters' => 'integer',
            'is_public' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }
}
