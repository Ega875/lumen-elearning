<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        // 1. Cek apakah di Headers Postman ada 'Authorization: Bearer <token>'
        $token = $request->bearerToken();

        if (!$token) {
            // Jika token tidak ada, tolak aksesnya!
            return response()->json([
                'success' => false,
                'message' => 'Token tidak ditemukan! Silakan login terlebih dahulu.'
            ], 401);
        }

        try {
            // 2. Bongkar Token menggunakan kunci rahasia dari .env
            $credentials = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));

            // 3. Cari user berdasarkan ID (sub) yang ada di dalam token
            $user = User::find($credentials->sub);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun tidak ditemukan!'
                ], 401);
            }

            // 4. Titipkan data user yang sedang login ini ke dalam Request
            // Agar bisa dipanggil di Controller manapun dengan auth()->user() atau $request->auth
            $request->auth = $user;

            // 5. Izinkan request melanjutkan perjalanan ke Controller
            return $next($request);

        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token sudah kadaluwarsa! Silakan login ulang.'
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid!'
            ], 401);
        }
    }
}