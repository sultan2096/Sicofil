<?php

namespace App\Models;

use App\Models\Concerns\AppliesCommuneScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectorTracking extends Model
{
    use HasFactory, AppliesCommuneScope;

    protected $table = 'collector_tracking';

    protected $fillable = [
        'collector_id',
        'commune_id',
        'latitude',
        'longitude',
        'altitude',
        'accuracy',
        'activity',
        'battery_level',
        'tracked_at'
    ];

    protected $casts = [
        'tracked_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'altitude' => 'decimal:2',
        'accuracy' => 'decimal:2',
    ];

    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    public function scopeRecent($query, $minutes = 30)
    {
        return $query->where('tracked_at', '>=', now()->subMinutes($minutes));
    }

    public function scopeByCollector($query, $collectorId)
    {
        return $query->where('collector_id', $collectorId);
    }
}
