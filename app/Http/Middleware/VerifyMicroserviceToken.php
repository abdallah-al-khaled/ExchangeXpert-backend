<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMicroserviceToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');

        if ($token !== 'Bearer ' . env('MICROSERVICE_API_TOKEN')) {
            return response()->json(['error' => 'Unauthorized' . env('MICROSERVICE_API_TOKEN') ], 401);
        }
        return $next($request);
    }
}
