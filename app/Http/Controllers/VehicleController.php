<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function getByRfidTag(string $rfidTag) {
        $vehicle = Vehicle::where('rfid_tag', $rfidTag)->first();

        if(is_null($vehicle)) {
            AuditController::create('ERROR: Get Vehicle&No vehicle with TAG ['.$rfidTag.'] found.');
            return response()->json(['error' => 'The requested vehicle doesn\'t exist.'], 404);
        } else {
            AuditController::create('Get Vehicle&rfid tag: '.$vehicle->rfid_tag);
            return new VehicleResource($vehicle);
        }
    }
}
