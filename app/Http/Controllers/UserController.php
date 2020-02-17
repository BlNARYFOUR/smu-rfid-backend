<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use \Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function get(Request $request) {
        $size = is_numeric($request->input('size')) ? $request->input('size') : 25;
        $size = $size <= 100 ? $size : 100;

        $searchTerms = $request->input('search');

        $query = null;

        if(is_null($searchTerms)) {
            $res = User::orderBy('first_name', 'ASC')->orderBy('middle_name', 'ASC')->orderBy('last_name', 'ASC')->paginate($size);

        } else {
            $query = null;
            $searchTerms = explode(' ', $searchTerms);

            foreach ($searchTerms as $id => $term) {
                if($id == 0) {
                    $query = User::where('first_name', 'LIKE', '%' . $term . '%');
                } else {
                    $query->unionAll(User::where('first_name', 'LIKE', '%' . $term . '%'));
                }

                $query->unionAll(User::where('middle_name', 'LIKE', '%' . $term . '%'));
                $query->unionAll(User::where('last_name', 'LIKE', '%' . $term . '%'));
                $query->unionAll(User::where('email', 'LIKE', '%' . $term . '%'));
            }

            try {
                $res = DB::query()
                    ->select(DB::raw('*, COUNT(id) as AMOUNT_OF_HITS'))
                    ->fromSub($query, 'x')
                    ->groupBy('id')
                    ->orderBy('AMOUNT_OF_HITS', 'DESC')
                    ->paginate($size);
            } catch (Exception $exception) {
                return response()->json(['error' => $exception->getMessage()], 503);
            }
        }

        return UserResource::collection($res);
    }
}
