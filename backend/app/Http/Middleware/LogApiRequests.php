<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000;

        // Log slow requests (>1000ms)
        if ($duration > 1000) {
            Log::channel('performance')->warning('Slow API request', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'duration_ms' => round($duration, 2),
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);
        }

        // Log all API requests in development
        if (app()->environment('local')) {
            Log::channel('api')->info('API Request', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status' => $response->getStatusCode(),
                'duration_ms' => round($duration, 2),
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);
        }

        // Log errors
        if ($response->getStatusCode() >= 400) {
            Log::channel('api')->error('API Error Response', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status' => $response->getStatusCode(),
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'response' => $response->getContent(),
            ]);
        }

        return $response;
    }
}
