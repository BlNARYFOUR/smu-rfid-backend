<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function get() {
        return response()->json(["data" => "TEST"], 200);
    }
}
