<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if(Auth::guard('sanctum')->user() && Auth::guard('sanctum')->user()->role === 'user'){
                return $next($request);
            }
            return response()->json(['message' => 'Unauthorized'], 403); 
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memverifikasi token',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
