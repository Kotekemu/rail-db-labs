<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteStop extends Model
{
    protected $table = 'rail.route_stops';
    protected $primaryKey = 'stop_id';
    public $timestamps = false;

    protected $fillable = [
        'route_id',
        'stop_no',
        'station_id',
        'arrival_time',
        'departure_time',
    ];

    protected $casts = [
        'arrival_time' => 'datetime',
        'departure_time' => 'datetime',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id', 'station_id');
    }
}
