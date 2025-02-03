<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Cek apakah token tersedia dalam request
            if (!$request->bearerToken()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token tidak ditemukan'
                ], 401);
            }

            // Ambil user berdasarkan token
            $user = JWTAuth::parseToken()->authenticate();

            // Jika user tidak ditemukan
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token valid, tetapi user tidak ditemukan'
                ], 401);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token telah kedaluwarsa'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak ditemukan atau tidak bisa diproses'
            ], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memverifikasi token',
                'error' => $th->getMessage()
            ], 500);
        }


        // Lanjutkan ke proses berikutnya jika token valid
        return $next($request);
     
    }
}
