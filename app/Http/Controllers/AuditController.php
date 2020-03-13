<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuditResource;
use App\Models\Audit;
use \Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function get(Request $request) {
        $size = is_numeric($request->input('size')) ? $request->input('size') : 25;
        $size = $size <= 100 ? $size : 100;

        $searchTerms = $request->input('search');

        $query = null;

        if(is_null($searchTerms)) {
            $res = Audit::orderBy('created_at', 'DESC')->paginate($size);

        } else {
            $query = null;
            $searchTerms = explode(' ', $searchTerms);

            foreach ($searchTerms as $id => $term) {
                if($id == 0) {
                    $query = Audit::whereHas('user', function($q) use ($term) {
                        $q->where('last_name', 'LIKE', '%' . $term . '%');
                    });
                } else {
                    $query->unionAll(Audit::whereHas('user', function($q) use ($term) {
                        $q->where('first_name', 'LIKE', '%' . $term . '%');
                    }));
                }

                $query->unionAll(Audit::whereHas('user', function($q) use ($term) {
                    $q->where('middle_name', 'LIKE', '%' . $term . '%');
                }));
                $query->unionAll(Audit::whereHas('user', function($q) use ($term) {
                    $q->where('last_name', 'LIKE', '%' . $term . '%');
                }));
                $query->unionAll(Audit::where('ip_address', 'LIKE', '%' . $term . '%'));
                $query->unionAll(Audit::where('user_first_name', 'LIKE', '%' . $term . '%'));
                $query->unionAll(Audit::where('user_middle_name', 'LIKE', '%' . $term . '%'));
                $query->unionAll(Audit::where('user_last_name', 'LIKE', '%' . $term . '%'));
            }

            try {
                $res = Audit::query()
                    ->select(DB::raw('*, COUNT(id) as AMOUNT_OF_HITS'))
                    ->fromSub($query, 'x')
                    ->groupBy('id')
                    ->orderBy('AMOUNT_OF_HITS', 'DESC')
                    ->orderBy('created_at', 'DESC')
                    ->paginate($size);
            } catch (Exception $exception) {
                return response()->json(['error' => $exception->getMessage()], 503);
            }
        }

        //return $res;
        return AuditResource::collection($res);
    }

    public static function create(string $action) {
        $audit = new Audit();
        $audit->action = $action;
        $audit->ip_address = request()->getClientIp();
        $audit->user_id = auth()->id();
        $audit->save();
    }
}
