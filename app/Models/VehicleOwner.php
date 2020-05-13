<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleOwner extends Model
{
    public function owner_type()
    {
        return $this->belongsTo(OwnerType::class);
    }
}
