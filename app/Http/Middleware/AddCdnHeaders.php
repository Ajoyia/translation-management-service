<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AddCdnHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('api/translations/export*')) {
            $response->headers->set('Cache-Control', 'public, max-age=3600, s-maxage=86400');
            $response->headers->set('Vary', 'Accept-Encoding');
            $response->headers->set('X-Cache-Status', 'MISS');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            
            if (config('app.cdn_url')) {
                $response->headers->set('X-CDN-URL', config('app.cdn_url'));
            }
        }

        return $response;
    }
}
