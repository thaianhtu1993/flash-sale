<?php

namespace App\Http\Middleware;

use App\AccessToken;
use Closure;
use Config;

class AppApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('app-token');

        if (empty($token)) {
            return response()->json(Config::get('constant.error.role'),401);
        }

        $accessToken = AccessToken::with('user')->where('token', $token)->first();
        if (empty($accessToken)) {
            return response()->json(Config::get('constant.error.role'),401);
        }

        if ($accessToken->isExpire()) {
            if ($accessToken->anonymous) {
                $accessToken->delete();
                return response()->json(
                    [
                        'status' => 3,
                        'message' => 'Bạn đã hết phiên sử dụng lần đăng nhập ẩn danh này'
                    ],401
                );
            }

            $accessToken->prepareRefresh();
            return response()->json(
                [
                    'status' => 2,
                    'message' => 'Token expire at '.$accessToken->expire_time. '. Please refresh token',
                    'code_refresh' => $accessToken->code_refresh
                ],
                200
            );
        }

        $request->attributes->add(['token' => $accessToken]);

        return $next($request);
    }
}
