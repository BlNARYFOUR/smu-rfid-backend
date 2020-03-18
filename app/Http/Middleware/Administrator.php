<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuditController;
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
        $user = auth()->user();

        if($user->admin) {
            return $next($request);
        } else {
            AuditController::create('ERROR: Authentication Level&Route: '.$request->path());
            return response()->json(["message" => "Unauthenticated. You are not an administrator."], 401);
        }
    }
}
