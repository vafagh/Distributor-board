<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ride extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    public function rideable()
    {
        return $this->belongsTo(Rideable::class);
    }
    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

}
