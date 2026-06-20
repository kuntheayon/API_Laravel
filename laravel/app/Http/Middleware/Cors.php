<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    protected array $allowedOrigins = [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost',
        'http://127.0.0.1',
    ];

    protected array $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    protected array $allowedHeaders = [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'Accept',
        'Origin',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('Origin');

        if ($origin && $this->isAllowedOrigin($origin)) {
            if ($request->getMethod() === 'OPTIONS') {
                $response = new Response('', 200);
            } else {
                $response = $next($request);
            }

            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods));
            $response->headers->set('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders));
            $response->headers->set('Access-Control-Expose-Headers', 'Authorization');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Vary', 'Origin');

            return $response;
        }

        return $next($request);
    }

    private function isAllowedOrigin(string $origin): bool
    {
        if (in_array($origin, $this->allowedOrigins, true)) {
            return true;
        }

        $parsed = parse_url($origin);
        $host = $parsed['host'] ?? '';

        return in_array($host, ['localhost', '127.0.0.1'], true);
    }
}
