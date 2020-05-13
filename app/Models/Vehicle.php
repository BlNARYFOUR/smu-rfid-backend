<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    public function vehicle_type()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function vehicle_owner()
    {
        return $this->belongsTo(VehicleOwner::class);
    }
}
