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
            $res = User::orderBy('id', 'DESC')->paginate($size);

        } else {
            $query = null;
            $subQuery = null;
            $searchTerms = explode(' ', $searchTerms);

            foreach ($searchTerms as $id => $term) {
                /*
                $res->orWhere('first_name', 'LIKE', '%'.$term.'%');
                $res->orWhere('middle_name', 'LIKE', '%'.$term.'%');
                $res->orWhere('last_name', 'LIKE', '%'.$term.'%');
                $res->orWhere('email', 'LIKE', '%'.$term.'%');
                */

                if($id == 0) {
                    $subQuery = User::where('first_name', 'LIKE', '%' . $term . '%');
                } else {
                    $subQuery->unionAll(User::where('first_name', 'LIKE', '%' . $term . '%'));
                }

                $subQuery->unionAll(User::where('middle_name', 'LIKE', '%' . $term . '%'));
                $subQuery->unionAll(User::where('last_name', 'LIKE', '%' . $term . '%'));
                $subQuery->unionAll(User::where('email', 'LIKE', '%' . $term . '%'));
            }

            //$query .= $subQuery . ") all_res GROUP BY id ORDER BY AMOUNT_OF_HITS DESC";

            //return $subQuery->paginate($size);

            //return $query->toSql();
            try {
                $res = DB::query()
                    ->select(DB::raw('*, COUNT(id) as AMOUNT_OF_HITS'))
                    ->fromSub($subQuery, 'x')
                    ->groupBy('id')
                    ->orderBy('AMOUNT_OF_HITS', 'DESC')
                    //->get()
                    ->paginate($size);
            } catch (Exception $exception) {
                return response()->json(['error' => $exception->getMessage()]);
            }

            //return response()->json(is_null($searchTerms) ? 'null' : $searchTerms);
        }

        return UserResource::collection($res);
    }

    public function search() {

    }
}
