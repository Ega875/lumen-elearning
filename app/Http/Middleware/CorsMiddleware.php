<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        // Daftarkan header yang diizinkan
        $headers = [
            'Access-Control-Allow-Origin'      => '*', // Mengizinkan akses dari semua URL/Port
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With'
        ];

        // Jika request berupa OPTIONS (Pre-flight check dari Axios), langsung kembalikan response 200
        if ($request->isMethod('OPTIONS')) {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        $response = $next($request);
        
        foreach($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}