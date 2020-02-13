<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function get() {
        return response()->json(["data" => "TEST"], 200);
    }

    public function getAuth() {
        return response()->json(["data" => "TEST_AUTH"], 200);
    }

    public function getAdmin() {
        return response()->json(["data" => "TEST_ADMIN"], 200);
    }
}
