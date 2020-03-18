<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\Audit;
use App\Models\User;
use \Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

                if(strpos('administrator', strtolower($term)) !== false) {
                    $query->unionAll(User::where('admin', true));
                } else if(strpos('officer in charge', strtolower($term)) !== false) {
                    $query->unionAll(User::where('admin', false));
                }
            }

            try {
                $res = User::query()
                    ->select(DB::raw('*, COUNT(id) as AMOUNT_OF_HITS'))
                    ->fromSub($query, 'x')
                    ->groupBy('id')
                    ->orderBy('AMOUNT_OF_HITS', 'DESC')
                    ->orderBy('first_name', 'ASC')
                    ->orderBy('middle_name', 'ASC')
                    ->orderBy('last_name', 'ASC')
                    ->paginate($size);
            } catch (Exception $exception) {
                return response()->json(['error' => $exception->getMessage()], 503);
            }
        }

        return UserResource::collection($res);
    }

    public function getById(int $id) {
        $user = User::find($id);

        if(is_null($user)) {
            return response()->json(['error' => 'The requested user doesn\'t exist.'], 404);
        } else {
            return new UserResource($user);
        }
    }

    public function delete(int $id) {

        if(Auth::user()->id !== $id) {

            $user = User::find($id);

            if(is_null($user)) {
                AuditController::create('ERROR: Delete User&Unknown user_id: '.$id);
                return response()->json(['error' => 'The requested user doesn\'t exist.'], 404);
            } else {
                $email = $user->email;
                $audits = Audit::where('user_id', $id)->get();

                foreach ($audits as $audit) {
                    $audit->user_first_name = $user->first_name;
                    $audit->user_middle_name = $user->middle_name;
                    $audit->user_last_name = $user->last_name;
                    $audit->save();
                }

                $user->delete();

                AuditController::create('Delete user&'.$email);
                return response()->json(['message' => 'The account has been deleted.']);
            }
        }

        AuditController::create('ERROR: Delete user&Tried to delete own account');
        return response()->json(['error' => 'As a safety measure, you cannot delete your own account.'], 403);
    }

    public function update(Request $request, int $id) {
        return $request->first_name; //$request->input('first_name');
    }
}
