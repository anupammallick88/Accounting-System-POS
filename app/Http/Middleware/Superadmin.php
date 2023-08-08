<?php

namespace App\Http\Middleware;

use Closure;

class Superadmin
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
        $administrator_list = config('constants.administrator_usernames');

        if (!empty($request->user()) && in_array(strtolower($request->user()->username), explode(',', strtolower($administrator_list)))) {
            return $next($request);
        } else {
            abort(403, 'Unauthorized action.');
        }
    }
}
