<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuditResource;
use App\Models\Audit;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function get(Request $request) {
        $size = is_numeric($request->input('size')) ? $request->input('size') : 25;
        $size = $size <= 100 ? $size : 100;

        return AuditResource::collection(Audit::orderBy('created_at', 'DESC')->paginate($size));
    }

    public static function create(string $action) {
        $audit = new Audit();
        $audit->action = $action;
        $audit->ip_address = request()->getClientIp();
        $audit->user_id = auth()->id();
        $audit->save();
    }
}
