<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Request\Pagination;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;

class PaginationResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return Pagination::class === $argument->getType() && Request::METHOD_GET === $request->getMethod();
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        $pagination = new Pagination(
            (int) $request->query->get('page', 1),
            (int) $request->query->get('per_page', 100),
            $request->query->get('sort', null),
            $request->query->get('sort_order', null)
        );

        // Used in response handler.
        $request->attributes->set('_pagination', $pagination);

        yield $pagination;
    }
}
