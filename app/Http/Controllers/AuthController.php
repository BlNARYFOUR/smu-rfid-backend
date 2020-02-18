<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthRegisterRequest;
use App\Http\Requests\AuthVerifyRequest;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(AuthRegisterRequest $request)
    {
        $user = null;

        try {
            $user = User::create([
                'email' => $request->email,
                'admin' => $request->admin,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'middle_name' => $request->middle_name,
                'password' => $request->password,
                'email_verify_token' => bcrypt($request->email),
            ]);
        } catch (QueryException $exception) {
            return response(['error' => 'Email is already in use.', 'exception' => $exception->getMessage()], 409);
        }

        $name = $user->first_name.' '.$user->middle_name;
        $name .= is_null($user->middle_name) ? $user->last_name : ' '.$user->last_name;
        $data = array('verificationCode' => $user->email_verify_token, 'email' => $user->email, 'name' => $name);

        Mail::send('userVerifyMail', $data, function($message) use ($user, $name) {
            $message->to($user->email, $name)->subject
            ('SMU RFID VMS : verify your email');
            $message->from('no-reply@smu.edu.ph','no-reply@smu.edu.ph');
        });

        return response()->json(['message' => 'Registration succeeded. When your account has been verified, you will receive an email.'], 201);
    }

    public function verify(AuthVerifyRequest $request)
    {
        $user = User::where('email_verify_token', $request->token)->first();

        if (!is_null($user)) {
            $user->email_verified_at = now();
            $user->email_verify_token = null;
            $user->save();

            return response()->json(['message' => 'User successfully verified.'], 202);
        }

        return response()->json(['error' => 'User could not be verified.'], 400);
    }

    public function login(AuthLoginRequest $request)
    {
        $credentials = ['email' => $request->email, 'password' => $request->password];

        $user = User::where('email', $request->email)->first();

        if(!is_null($user) && is_null($user->email_verified_at)) {
            return response()->json(['error' => 'Email has not yet been verified. Open your email and click the verification link.'], 401);
        }

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
