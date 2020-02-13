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
                //'verify_token' => bcrypt($request->email),
            ]);
        } catch (QueryException $exception) {
            return response(['error' => 'Email is already in use.'], 409);
        }

        /*
        $data = array('verificationCode' => $user->verify_token, 'email' => $user->email, 'name' => $user->name);

        Mail::send('userVerifyMail', $data, function($message) {
            $message->to('lambertbrend@gmail.com', 'BinaryFour')->subject
            ('blog.binaryfour.be - verify user');
            $message->from('info@binaryfour.be','info@binaryfour.be');
        });
        */

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
