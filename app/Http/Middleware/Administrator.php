<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class Administrator extends BaseMiddleware
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
        $user = $this->auth->parseToken()->user();

        if($user->admin) {
            return $next($request);
        } else {
            return response()->json(["message" => "Unauthenticated. You are not an administrator."], 401);
        }
    }
}
