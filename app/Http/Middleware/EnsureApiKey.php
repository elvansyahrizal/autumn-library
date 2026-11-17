<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiKey
{
    /**
     * Validasi header X-API-Key untuk endpoint API yang bersifat menulis.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.api_key');

        if ($expected === '' || ! hash_equals($expected, (string) $request->header('X-API-Key'))) {
            return response()->json([
                'message' => 'Unauthorized. Sertakan header X-API-Key yang valid.',
            ], 401);
        }

        return $next($request);
    }
}
