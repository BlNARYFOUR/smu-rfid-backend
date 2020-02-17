<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function get(Request $request) {
        $size = is_numeric($request->input('size')) ? $request->input('size') : 25;
        $size = $size <= 100 ? $size : 100;

        $searchTerms = $request->input('search');

        $res = null;

        if(is_null($searchTerms)) {
            $res = User::orderBy('id', 'DESC')->paginate($size);

        } else {
            $searchTerms = explode(' ', $searchTerms);
            $res = User::query();
            foreach ($searchTerms as $term) {
                $res->orWhere('first_name', 'LIKE', '%'.$term.'%');
                $res->orWhere('middle_name', 'LIKE', '%'.$term.'%');
                $res->orWhere('last_name', 'LIKE', '%'.$term.'%');
                $res->orWhere('email', 'LIKE', '%'.$term.'%');
            }

            $res = $res->paginate($size);
            //return response()->json(is_null($searchTerms) ? 'null' : $searchTerms);
        }

        return UserResource::collection($res);
    }

    public function search() {

    }
}
