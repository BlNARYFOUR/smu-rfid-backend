<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $user = null;

        try {
            $user = User::create([
                'email' => $request->email,
                'name' => $request->name,
                'password' => $request->password,
                'email_verify_token' => bcrypt($request->email),
            ]);
        } catch (QueryException $exception) {
            return response(['error' => 'Email is already in use.'], 409);
        }

        $name = $user->first_name.' '.$user->middle_name;
        $name .= is_null($user->middle_name) ? $user->last_name : ' '.$user->last_name;
        $data = array('verificationCode' => $user->verify_token, 'email' => $user->email, 'name' => $name);

        Mail::send('userVerifyMail', $data, function($message) use ($user, $name) {
            $message->to($user->email, $name)->subject
            ('SMU RFID VM : verify your email');
            $message->from('no-reply@smu.edu.ph','no-reply@smu.edu.ph');
        });

        return response()->json(['message' => 'Registration succeeded. When your account has been verified, you will receive an email.'], 201);
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        $user = User::where('email', request('email'))->first();

        /*
        if(!is_null($user) && is_null($user->verified_at)) {
            return response()->json(['error' => 'User has not yet been verified.'], 401);
        }
        */

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Email and password do not match.'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60
        ]);
    }
}
