<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleOwnerCreateRequest;
use App\Http\Resources\VehicleOwnerResource;
use App\Models\VehicleOwner;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class VehicleOwnerController extends Controller
{
    public function getById(int $id) {
        $user = VehicleOwner::find($id);

        if(is_null($user)) {
            AuditController::create('ERROR: Get Vehicle Owner&No vehicle owner with ID ['.$id.'] found.');
            return response()->json(['error' => 'The requested vehicle owner doesn\'t exist.'], 404);
        } else {
            AuditController::create('Get User&email: '.$user->email);
            return new VehicleOwnerResource($user);
        }
    }

    public function getVehicleOwnerImage($id) {
        $buf = VehicleOwner::find($id, 'picture');

        if(is_null($buf)) {
            return response()->json(['error' => 'Not a valid vehicle_owner ID'], 400);
        } else {
            $filename = $buf['picture'];
            $file = Storage::get($filename);
            $type = Storage::mimeType($filename);
            $response = Response::make($file, 200);
            $response->header("Content-Type", $type);
        }

        return $response;
    }

    public function newVehicleOwner(VehicleOwnerCreateRequest $request) {
        $isVip = $request->input('is_vip');
        $firstName = $request->input('first_name');
        $lastName = $request->input('last_name');
        $middleName = $request->input('middle_name');
        $idNumber = $request->input('id_number');
        $phoneNumber = $request->input('phone_number');
        $address = $request->input('address');
        $ownerType = $request->input('owner_type');
        $picture = $request->file('picture');

        $imageName = $picture->store('images/vehicle_owners');

        $vehicleOwner = new VehicleOwner();

        $vehicleOwner->is_vip = $isVip;
        $vehicleOwner->first_name = $firstName;
        $vehicleOwner->last_name = $lastName;
        $vehicleOwner->middle_name = $middleName;
        $vehicleOwner->id_number = $idNumber;
        $vehicleOwner->phone_number = $phoneNumber;
        $vehicleOwner->address = $address;
        $vehicleOwner->owner_type_id = $ownerType;
        $vehicleOwner->picture = $imageName;

        try {
            $vehicleOwner->save();
        } catch (QueryException $exception) {
            Storage::delete($imageName);
            return response()->json(['error' => 'Something went wrong. Please try again later.'], 406);
        }

        return response()->json(['message' => 'new vehicle owner added', 'id' => $vehicleOwner->id]);
    }
}
