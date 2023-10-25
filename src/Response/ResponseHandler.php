<?php

declare(strict_types=1);

namespace App\Response;

use App\Service\RequestService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ResponseHandler
{
    protected SerializerInterface $serializer;
    protected RequestStack $requestStack;
    private RequestService $requestService;

    public function __construct(
        SerializerInterface $serializer,
        RequestStack $requestStack,
        RequestService $requestService
    ) {
        $this->serializer = $serializer;
        $this->requestStack = $requestStack;
        $this->requestService = $requestService;
    }

    public function handleViolations(ConstraintViolationListInterface $constraintViolationList) : JsonResponse
    {
        $errors = [];
        foreach ($constraintViolationList as $error) {
            $path = $error->getPropertyPath();
            if (isset($errors[$path])) {
                $errors[$path][] = $error->getMessage();
            } else {
                $errors[$path] = [$error->getMessage()];
            }
        }

        $list = [];
        foreach ($errors as $path => $message) {
            if (!$path) {
                if (is_array($message) && count($message) === 1) {
                    $message = $message[0];
                }
                $list[] = $message;
            } else {
                $property = new PropertyPath($path);
                $current = &$list;
                foreach ($property->getElements() as $element) {
                    $element = "$element";
                    if (!isset($current[$element])) {
                        $current[$element] = [];
                    }
                    $current = &$current[$element];
                }
                $current = $message;    
            }
        }

        $content = [
            'error' => 'InvalidRequest',
            'messages' => $list,
        ];

        return new JsonResponse($content, 400);
    }

    /**
     * @param null $data
     *
     * @throws ExceptionInterface
     */
    public function handle($data = null, array $headers = [], int $statusCode = null): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $groups = array_merge([$request->attributes->get('_restricted_group') ?? 'default'], $this->requestService->getExpandData());

        $result = [];
        if (Request::METHOD_PATCH === $request->getMethod()) {
            if (null === $data) {
                return new JsonResponse(null, $statusCode ?? 204);
            }
            $result = $this->serializer->normalize($data, 'json', compact('groups'));

            return new JsonResponse($result, $statusCode ?? 200);
        }

        if (Request::METHOD_GET === $request->getMethod()) {
            if ($data instanceof Paginator) {
                $pagination = $request->attributes->get('_pagination');
                $result = [
                    'count' => count($data),
                    'page' => (int) $pagination->getPage(),
                    'per_page' => (int) $pagination->getPerPage(),
                ];
            }
        }

        if ($data) {
            $object = $this->serializer->normalize($data, 'json', compact('groups'));
            $result = $data instanceof Paginator ? array_merge($result, ['data' => $object]) : $object;
        }

        return new JsonResponse($result, $statusCode ?? 200, $headers);
    }
}
