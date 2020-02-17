<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    /*
     * Unauthenticated request test.
     */
    public function get() {
        return response()->json(["data" => "TEST"], 200);
    }

    /*
     * low level authentication test.
     */
    public function getAuth() {
        return response()->json(["data" => "TEST_AUTH"], 200);
    }

    /*
     * Admin permission type authentication test.
     */
    public function getAdmin() {
        return response()->json(["data" => "TEST_ADMIN"], 200);
    }
}
