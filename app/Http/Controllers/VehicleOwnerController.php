<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleOwnerCreateRequest;
use App\Http\Resources\VehicleOwnerResource;
use App\Models\VehicleOwner;
use \Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class VehicleOwnerController extends Controller
{
    public function get(Request $request) {
        $size = is_numeric($request->input('size')) ? $request->input('size') : 25;
        $size = $size <= 100 ? $size : 100;

        $searchTerms = $request->input('search');
        $searchBuf = '';

        $query = null;

        if(is_null($searchTerms)) {
            $res = VehicleOwner::orderBy('first_name', 'ASC')->orderBy('middle_name', 'ASC')->orderBy('last_name', 'ASC')->paginate($size);

        } else {
            $query = null;
            $searchBuf = $searchTerms;
            $searchTerms = explode(' ', $searchTerms);

            foreach ($searchTerms as $id => $term) {
                if($id == 0) {
                    $query = VehicleOwner::where('first_name', 'LIKE', '%' . $term . '%');
                } else {
                    $query->unionAll(VehicleOwner::where('first_name', 'LIKE', '%' . $term . '%'));
                }

                $query->unionAll(VehicleOwner::where('middle_name', 'LIKE', '%' . $term . '%'));
                $query->unionAll(VehicleOwner::where('last_name', 'LIKE', '%' . $term . '%'));
                $query->unionAll(VehicleOwner::where('id_number', 'LIKE', '%' . $term . '%'));
                $query->unionAll(VehicleOwner::where('phone_number', 'LIKE', '%' . $term . '%'));
                $query->unionAll(VehicleOwner::where('address', 'LIKE', '%' . $term . '%'));

                $query->unionAll(VehicleOwner::select('vehicle_owners.*')
                    ->join('owner_types', 'owner_types.id', '=', 'vehicle_owners.owner_type_id')
                    ->where('owner_types.name', 'LIKE', '%' . $term . '%')
                );

                if(strpos('vip', strtolower($term)) !== false) {
                    $query->unionAll(VehicleOwner::where('is_vip', true));
                }
            }

            try {
                $res = VehicleOwner::query()
                    ->select(DB::raw('*, COUNT(id) as AMOUNT_OF_HITS'))
                    ->fromSub($query, 'x')
                    ->groupBy('id')
                    ->orderBy('AMOUNT_OF_HITS', 'DESC')
                    ->orderBy('first_name', 'ASC')
                    ->orderBy('middle_name', 'ASC')
                    ->orderBy('last_name', 'ASC')
                    ->paginate($size);
            } catch (Exception $exception) {
                AuditController::create('ERROR: Get Vehicle Owners&Exception: '.$exception->getMessage());
                return response()->json(['error' => $exception->getMessage()], 503);
            }
        }

        AuditController::create('Get Vehicle Owners' . ($searchBuf ? '&Searched on: '.$searchBuf : ''));
        return VehicleOwnerResource::collection($res);
    }

    public function getById(int $id) {
        $vehicleOwner = VehicleOwner::find($id);

        if(is_null($vehicleOwner)) {
            AuditController::create('ERROR: Get Vehicle Owner&No vehicle owner with ID ['.$id.'] found.');
            return response()->json(['error' => 'The requested vehicle owner doesn\'t exist.'], 404);
        } else {
            AuditController::create('Get Vehicle Owner&Full name: '.$vehicleOwner->first_name.' '.$vehicleOwner->middle_name.(is_null($vehicleOwner->middle_name) ? '' : ' ').$vehicleOwner->last_name);
            return new VehicleOwnerResource($vehicleOwner);
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
