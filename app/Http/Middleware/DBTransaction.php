<?php

namespace App\Http\Middleware;

use Closure;
use DB;

class DBTransaction
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
        DB::beginTransaction();
        $response = $next($request);
        if (!empty($response->exception)) {
            DB::rollBack();
            \Log::error($response->exception->getMessage());
            return $response;
        }

        DB::commit();
        return $response;
    }
}
