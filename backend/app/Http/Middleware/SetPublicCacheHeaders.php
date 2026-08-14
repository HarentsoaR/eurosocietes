<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPublicCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof Response && $response->getStatusCode() < 400) {
            $response->headers->set('Cache-Control', 'public, max-age=600');
        }

        return $response;
    }
}
