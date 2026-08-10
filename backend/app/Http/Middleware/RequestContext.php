<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generates a request ID and binds structured context to the log
 * (request id, url, method, authenticated user id when present).
 */
class RequestContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $this->requestId($request);

        $context = [
            'request_id' => $requestId,
            'path' => $request->path(),
            'method' => $request->method(),
        ];

        if ($user = $request->user()) {
            $context['user_id'] = $user->id;
        }

        Log::withContext($context);

        $response = $next($request);

        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }

    /**
     * Resolve a request id: echo a client-supplied UUID, otherwise generate one.
     */
    protected function requestId(Request $request): string
    {
        $header = $request->header('X-Request-ID');

        if (is_string($header) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $header)) {
            return $header;
        }

        return (string) Str::uuid();
    }
}
