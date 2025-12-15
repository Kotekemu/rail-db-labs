<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainType extends Model
{
    protected $table = 'rail.train_types';
    protected $primaryKey = 'train_type_id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
    ];
}
