<?php

namespace App\Http\Middleware;

use Closure;
use Config;

class AdminAccess
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
        $accessToken = \Request::get('token');

        if($accessToken->anonymous || $accessToken->user->role != 'admin') {
            return response()->json(Config::get('constant.error.role'),401);
        }

        return $next($request);
    }
}
