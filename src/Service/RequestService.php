<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class RequestService
{
    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Return if we need to return sub objects related to the main objects.
     */
    public function getExpandData()
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($expand = $request->headers->get('X-Expand-Data') ?: []) {
            $expand = explode(',', $expand);
            $expand = array_map('trim', $expand);
        }

        return $expand;
    }
}