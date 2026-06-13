<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * Authenticate Band agent requests with a shared API key.
     *
     * The key may be supplied either via the `X-API-Key` header or as a
     * Bearer token in the `Authorization` header. Compared against
     * `config('services.band.api_key')` (BAND_API_KEY in .env).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('services.band.api_key');

        if (empty($expected)) {
            return response()->json([
                'success' => false,
                'message' => 'API key authentication is not configured on the server.',
            ], 500);
        }

        $provided = $request->header('X-API-Key') ?? $request->bearerToken();

        if (! is_string($provided) || ! hash_equals($expected, $provided)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing API key.',
            ], 401);
        }

        return $next($request);
    }
}
