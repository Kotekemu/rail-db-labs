<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusyDepartureStation extends Model
{
    protected $table = 'rail.v_busy_departure_stations';
    protected $primaryKey = 'station_id';
    public $incrementing = false;
    public $timestamps = false;
}
