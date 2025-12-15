<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $table = 'rail.stations';
    protected $primaryKey = 'station_id';
    public $timestamps = false;

    protected $fillable = ['name', 'inn', 'address'];
}
