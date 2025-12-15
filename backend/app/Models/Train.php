<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Train extends Model
{
    protected $table = 'rail.trains';
    protected $primaryKey = 'train_id';
    public $timestamps = false;

    protected $fillable = [
        'owner_station_id',
        'train_number',
        'train_type_id',
        'name',
    ];

    public function type()
    {
        return $this->belongsTo(TrainType::class, 'train_type_id', 'train_type_id');
    }

    public function ownerStation()
    {
        return $this->belongsTo(Station::class, 'owner_station_id', 'station_id');
    }
}
