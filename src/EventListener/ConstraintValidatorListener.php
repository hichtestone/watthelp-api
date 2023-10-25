<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Annotation\ConstraintValidator;
use App\Exceptions\ConstraintValidatorException;
use App\Response\ResponseHandler;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConstraintValidatorListener implements EventSubscriberInterface
{

    protected Reader $reader;

    protected RequestStack $requestStack;

    private ResponseHandler $responseHandler;

    private ValidatorInterface $validator;

    private SerializerInterface $serializer;

    public function __construct(
        Reader $reader,
        RequestStack $stack,
        ResponseHandler $responseHandler,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    )
    {
        $this->reader = $reader;
        $this->requestStack = $stack;
        $this->responseHandler = $responseHandler;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => [
                ['onKernelController', -1],
            ],
        ];
    }

    /**
     * @param ControllerArgumentsEvent $event
     * @return JsonResponse|void
     * @throws ConstraintValidatorException
     * @throws \ReflectionException
     */
    public function onKernelController(ControllerArgumentsEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();
        $methodName = '__invoke';

        if (is_array($controller)) {
            list($controller, $methodName) = $controller;
        }

        $controllerReflectionObject = new \ReflectionObject($controller);
        $reflectionMethod = $controllerReflectionObject->getMethod($methodName);

        /** @var ConstraintValidator $methodAnnotation */
        $methodAnnotation = $this->reader->getMethodAnnotation($reflectionMethod, ConstraintValidator::class);

        if ($methodAnnotation) {
            $requestData = in_array($request->getMethod(), [Request::METHOD_GET], true)
                ? $request->query->all()
                : array_merge($request->request->all(), $request->files->all(), $request->query->all());

            $entity = isset($methodAnnotation->options['type']) ? array_filter($event->getArguments(), fn ($arg) => $arg instanceof $methodAnnotation->options['type']) : null;
            $entity = $entity ? array_pop($entity) : null;
            $options = array_merge([
                'entity' => $entity,
                'request' => $request
            ], $methodAnnotation->options);

            $violations = $this->validator->validate($requestData, new $methodAnnotation->class($options));
            if (\count($violations)) {
                throw new ConstraintValidatorException($violations);
            }
        }
    }
}
