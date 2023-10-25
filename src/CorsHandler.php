<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsHandler
{

    /**
     * @throws \InvalidArgumentException
     */
    public function handle(Request $request)
    {
        // skip if not a CORS request
        if (
            !$request->headers->has('Origin') ||
            $request->headers->get('Origin') == $request->getSchemeAndHttpHost()
        ) {
            return;
        }
        // perform preflight checks
        if ('OPTIONS' === $request->getMethod()) {
            return $this->getPreflightResponse($request);
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function getPreflightResponse(Request $request): Response
    {
        $response = new Response();
        $response->headers->set(
            'Access-Control-Allow-Methods',
            'GET, POST, PUT, PATCH, DELETE, OPTIONS'
        );
        $response->headers->set(
            'Access-Control-Allow-Headers',
            'Content-Type, X-Requested-With, Authorization, X-Expand-Data, If-Match, X-Mercure-Uri, Accept-Version'
        );
        $response->headers->set('Access-Control-Max-Age', 3600);
        $response->headers->set(
            'Access-Control-Allow-Origin',
            $request->headers->get('Origin')
        );

        return $response;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function apply(Response $response, Request $request): void
    {
        $response->headers->set(
            'Access-Control-Expose-Headers',
            ['Authorization', 'X-Expand-Data', 'ETag', 'X-Mercure-Uri']
        );
        $response->headers->set(
            'Access-Control-Allow-Origin',
            $request->headers->get('Origin')
        );
    }
}