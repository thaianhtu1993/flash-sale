<?php

namespace App\Http\Middleware;

use Closure;
use Config;

class UserAccess
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
        $utilities = \App::make('Utilities');
        $accessToken = \Request::get('token');
        if($accessToken->anonymous || in_array($accessToken->user->role,$utilities->userAccess()) == false) {
            return response()->json(Config::get('constant.error.role'),401);
        }

        return $next($request);
    }
}
