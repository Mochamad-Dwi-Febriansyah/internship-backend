<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         // Cek jika token ada di header
         if (!$request->bearerToken()) {
            return response()->json([
                'message' => 'Token tidak ditemukan'
            ], 401);
        }

        // Validasi token dengan Sanctum
        try {
            // Menggunakan token untuk memverifikasi akses
            $user = Auth::guard('sanctum')->user();
            // dd($user);
            if (!$user) {
                return response()->json([
                    'message' => 'Token tidak valid atau telah kadaluarsa'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memverifikasi token',
                'error' => $e->getMessage()
            ], 500);
        }

        // Lanjutkan ke proses berikutnya jika token valid
        return $next($request);
     
    }
}
