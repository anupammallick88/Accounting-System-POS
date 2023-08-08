<?php

namespace App\Http\Middleware;

use Closure;
use Modules\Ecommerce\Entities\EcomApiSetting;

class EcomApi
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
        $token = $request->header('API-TOKEN');
        $is_api_settings_exists = EcomApiSetting::where('api_token', $token)
                                            // ->where('shop_domain', $shop_domain)
                                            ->exists();

        if (!$is_api_settings_exists) {
            die('Invalid Request');
        }
        return $next($request);
    }
}
