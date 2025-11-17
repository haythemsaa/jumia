<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVendor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isVendor()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Vendor access only.',
            ], 403);
        }

        // Check if vendor is approved
        if ($request->user()->vendor && !$request->user()->vendor->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Your vendor account is pending approval.',
            ], 403);
        }

        return $next($request);
    }
}
