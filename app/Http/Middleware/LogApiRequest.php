<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('api_request', [
            'user_id' => $request->user()?->id,
            'endpoint' => $request->method().' '.$request->path(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        return $next($request);
    }
}
