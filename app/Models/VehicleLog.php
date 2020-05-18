<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleLog extends Model
{
    public $timestamps = false;

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
