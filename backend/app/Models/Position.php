<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'rail.positions';
    protected $primaryKey = 'position_id';
    public $timestamps = false;

    protected $fillable = ['name'];
}
