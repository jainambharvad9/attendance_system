<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AttendanceLog extends Model
{
    protected $fillable = [
        'user_id',
        'location_id',
        'latitude',
        'longitude',
        'accuracy',
        'distance_meters',
        'selfie_path',
        'status',
        'marked_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'accuracy' => 'decimal:2',
            'distance_meters' => 'decimal:2',
            'marked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function getSelfieUrlAttribute(): string
    {
        if (!$this->selfie_path) {
            return 'https://placehold.co/120x120?text=Photo';
        }

        return Storage::disk('public')->url($this->selfie_path);
    }
}
