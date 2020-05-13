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
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function get(Request $request) {
        $size = is_numeric($request->input('size')) ? $request->input('size') : 25;
        $size = $size <= 100 ? $size : 100;

        $searchTerms = $request->input('search');
        $searchBuf = '';

        $query = null;

        if(is_null($searchTerms)) {
            $res = User::orderBy('first_name', 'ASC')->orderBy('middle_name', 'ASC')->orderBy('last_name', 'ASC')->paginate($size);

        } else {
            $query = null;
            $searchBuf = $searchTerms;
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
                AuditController::create('ERROR: Get Users&Exception: '.$exception->getMessage());
                return response()->json(['error' => $exception->getMessage()], 503);
            }
        }

        AuditController::create('Get Users' . ($searchBuf ? '&Searched on: '.$searchBuf : ''));
        return UserResource::collection($res);
    }

    public function getById(int $id) {
        $user = User::find($id);

        if(is_null($user)) {
            AuditController::create('ERROR: Get User&No user with ID ['.$id.'] found.');
            return response()->json(['error' => 'The requested user doesn\'t exist.'], 404);
        } else {
            AuditController::create('Get User&email: '.$user->email);
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

                AuditController::create('Delete user&email: '.$email);
                return response()->json(['message' => 'The account has been deleted.']);
            }
        }

        AuditController::create('ERROR: Delete user&Tried to delete own account');
        return response()->json(['error' => 'As a safety measure, you cannot delete your own account.'], 403);
    }

    public function update(UserUpdateRequest $request, int $id) {
        $user = User::find($id);

        if(is_null($user)) {
            AuditController::create('ERROR: Update User&No user with ID ['.$id.'] found.');
            return response()->json(['error' => 'The requested user doesn\'t exist.'], 404);
        }

        $emailChanged = false;

        $admin = $request->input('admin');
        $firstName = $request->input('first_name');
        $lastName = $request->input('last_name');
        $middleName = $request->input('middle_name');
        $email = $request->input('email');
        $password = $request->input('password');

        try {
            $user->admin = $admin;
            $user->first_name = $firstName;
            $user->last_name = $lastName;
            $user->middle_name = $middleName;

            if(!is_null($password)) {
                $user->password = $password;
            }

            $emailChanged = $user->email !== $email;
            $user->email = $email;

            if($emailChanged) {
                $user->email_verify_token = bcrypt($email);
                $user->email_verified_at = null;
            }

            $user->save();
        } catch (QueryException $exception) {
            AuditController::create('ERROR: Update User&Email already in use: '.$email);
            return response(['error' => 'Email is already in use.', 'exception' => $exception->getMessage()], 409);
        }

        if($emailChanged) {
            $name = $user->first_name.' '.$user->middle_name;
            $name .= is_null($user->middle_name) ? $user->last_name : ' '.$user->last_name;
            $data = array('verificationCode' => $user->email_verify_token, 'email' => $user->email, 'name' => $name);

            Mail::send('userVerifyMail', $data, function($message) use ($user, $name) {
                $message->to($user->email, $name)->subject
                ('SMU RFID VMS : verify your email');
                $message->from('no-reply@smu.edu.ph','no-reply@smu.edu.ph');
            });

            AuditController::create('Update User&'.$user->email);
            return response()->json(['message' => 'Update succeeded. Open your email inbox to verify your new email.'], 200);
        }

        AuditController::create('Update User&'.$user->email);
        return response()->json(['message' => 'Update succeeded.'], 200);
    }
}
