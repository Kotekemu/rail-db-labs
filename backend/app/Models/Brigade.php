<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brigade extends Model
{
    protected $table = 'rail.brigades';
    protected $primaryKey = 'brigade_id';
    public $timestamps = false;

    protected $fillable = ['name'];
}
