<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $jwt = $request->bearerToken();
        if (is_null($jwt)){
            return response()->json(['Akses Ditolak'], 422);
        }

        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));
        if ($decode->role == 'admin'||$decode->role == 'user') {
            return $next($request);
        } 
        
        return response()->json(['Anda tidak memiliki hak akses admin'], 422);
    }
}