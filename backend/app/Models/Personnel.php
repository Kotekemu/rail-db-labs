<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    protected $table = 'rail.personnel';
    protected $primaryKey = 'person_id';
    public $timestamps = false;

    protected $fillable = [
        'station_id',
        'inn',
        'full_name',
        'position_id',
        'brigade_id',
        'hire_date',
        'fired_at',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id', 'station_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'position_id');
    }

    public function brigade()
    {
        return $this->belongsTo(Brigade::class, 'brigade_id', 'brigade_id');
    }
}
