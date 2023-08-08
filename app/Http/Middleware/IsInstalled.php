<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use Closure;

class IsInstalled
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
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return redirect(url('/') . '/install');
        } else {
            // if (!Cache::has('callback')) {
            //     $ch = curl_init(); $request_url = base64_decode('aHR0cHM6Ly9sLnVsdGltYXRlZm9zdGVycy5jb20vYXBpL3R5cGVfMw=='); $callback = 0;

            //     $curlConfig = [CURLOPT_URL => $request_url, 
            //         CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYHOST => false, CURLOPT_SSL_VERIFYPEER => false,
            //         CURLOPT_POSTFIELDS => [
            //             'url' => url("/"),
            //             'path' => app_path(),
            //             'license_code' => config('author.envato_purchase_code', 0),
            //             'email' => config('mail.from.address'),
            //             'product_id' => config('author.pid', 0)
            //         ]
            //     ];

            //     curl_setopt_array($ch, $curlConfig);
            //     $result = curl_exec($ch);
            //     curl_close($ch);

            //     if($result){$result = json_decode($result, true);
            //         if($result['flag'] == 'valid'){$callback = 1; } elseif(isset($result['data']) && isset($result['data']['action']) && $result['data']['action'] == 'r'){$callback = 'r';}}

            //     if($callback){Cache::put('callback', $callback, 24*60*60*1000);} else {Cache::put('callback', $callback, 7200);}
            // } else {
            //     $c = Cache::get('callback');if($c === 'r'){die();}elseif (!$c) {die();}
            // }
        }
        
        return $next($request);
    }
}
