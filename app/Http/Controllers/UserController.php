<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function get(Request $request) {
        $size = is_numeric($request->input('size')) ? $request->input('size') : 6;
        $size = $size <= 20 ? $size : 20;

        $res = User::orderBy('id', 'DESC')->paginate($size);

        return UserResource::collection($res);
    }

    public function search() {

    }
}
