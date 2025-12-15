<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $table = 'rail.routes';
    protected $primaryKey = 'route_id';
    public $timestamps = false;

    protected $fillable = [
        'route_code',
        'owner_station_id',
        'train_id',
        'departure_station_id',
        'arrival_station_id',
        'scheduled_departure',
        'scheduled_arrival',
        'brigade_id',
    ];

    protected $casts = [
        'scheduled_departure' => 'datetime',
        'scheduled_arrival' => 'datetime',
    ];

    public function stops()
    {
        return $this->hasMany(RouteStop::class, 'route_id', 'route_id')
            ->orderBy('stop_no');
    }
}
