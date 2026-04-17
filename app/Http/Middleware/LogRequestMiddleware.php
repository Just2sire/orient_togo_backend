<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // On évite de logger les données sensibles
        $payload = $request->except(['password', 'password_confirmation', 'code', 'token', 'access_token']);

        Log::channel('requests')->info('Frontend Request', [
            'method'  => $request->method(),
            'url'     => $request->fullUrl(),
            'ip'      => $request->ip(),
            'status'  => $response->getStatusCode(),
            'payload' => $payload,
            'user_id' => $request->user()?->id,
            'duration' => defined('LARAVEL_START') ? round((microtime(true) - LARAVEL_START) * 1000, 2) . 'ms' : null,
        ]);

        return $response;
    }
}
