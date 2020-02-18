<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class Administrator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //$this->authenticate($request);
        $user = Auth::user();

        if($user->admin) {
            return $next($request);
        } else {
            return response()->json(["message" => "Unauthenticated. You are not an administrator."], 401);
        }
    }
}
