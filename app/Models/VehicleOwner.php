<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleOwner extends Model
{
    public function ownerType()
    {
        return $this->belongsTo(OwnerType::class);
    }
}
