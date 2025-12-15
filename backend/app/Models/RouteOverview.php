<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteOverview extends Model
{
    protected $table = 'rail.v_route_overview';
    protected $primaryKey = 'route_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'scheduled_departure' => 'datetime',
        'scheduled_arrival' => 'datetime',
    ];
}
