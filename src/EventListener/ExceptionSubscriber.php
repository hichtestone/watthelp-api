<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exceptions\ConstraintValidatorException;
use App\Response\ResponseHandler;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    protected ?LoggerInterface $logger;

    private ResponseHandler $responseHandler;
    private TranslatorInterface $translator;

    /**
     * @var string
     */
    private string $env;

    public function __construct(
        ResponseHandler $responseHandler,
        string $env,
        TranslatorInterface $translator,
        LoggerInterface $logger = null
    )
    {
        $this->logger = $logger;
        $this->responseHandler = $responseHandler;
        $this->env = $env;
        $this->translator = $translator;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => [
                ['onKernelException', 0],
            ],
        ];
    }

    /**
     * Transform Exception to a valid json response.
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $response = null;
        $message = 'An error has occurred.';
        $code = 500;

        switch (true) {
            case $exception instanceof ConstraintValidatorException:
                $this->logException($exception, LogLevel::INFO);
                $response = $this->responseHandler->handleViolations($exception->getViolations());
                break;
            case $exception instanceof UnauthorizedHttpException:
                $this->logException($exception, LogLevel::INFO);
                $message = [
                    'error' => 'UnAuthorized',
                    'messages' => ['dev' === $this->env ? $exception->getMessage() : $this->translator->trans('unauthorized_call')],
                ];
                $code = 401;
                break;
            case $exception instanceof AccessDeniedHttpException:
                $this->logException($exception, LogLevel::INFO);
                $message = [
                    'error' => 'AccessDenied',
                    'messages' => ['dev' === $this->env ? $exception->getMessage() : $this->translator->trans('access_denied')],
                ];
                $code = 403;
                break;
            case $exception instanceof NotFoundHttpException:
                $this->logException($exception, LogLevel::INFO);
                $message = [
                    'error' => 'ResourceNotFound',
                    'messages' => ['dev' === $this->env ? $exception->getMessage() : $this->translator->trans('resource_not_found')],
                ];
                $code = 404;
                break;
            case $exception instanceof MethodNotAllowedHttpException:
                $this->logException($exception, LogLevel::INFO);
                $message = [
                    'error' => 'MethodNotAllowed',
                    'messages' => ['dev' === $this->env ? $exception->getMessage() : $this->translator->trans('method_not_allowed')],
                ];
                $code = 404;
                break;
            case BadRequestHttpException::class:
                $this->logException($exception, LogLevel::DEBUG);
                $code = 400;
                $message = [
                    'error' => 'Bad Request',
                    'messages' => $exception->getMessage(),
                ];
                break;
            default:
                $this->logException($exception, LogLevel::CRITICAL);
                $message = [
                    'error' => 'InternalServerError',
                    'messages' => ['dev' === $this->env || 'test' === $this->env ? $exception->getMessage() : $this->translator->trans('unknown_error_has_occurred')],
                ];
                $code = 500;
                break;
        }

        try {
            if (null === $response) {
                $response = new JsonResponse($message, $code);
            }

            $event->setResponse($response);
        } catch (\InvalidArgumentException $e) {
            $this->logException($e, LogLevel::ERROR);
        }
    }

    /**
     * @param \Exception $exception
     * @param $type
     */
    protected function logException($exception, $type, bool $previous = false)
    {
        $message = ($previous ? 'Previous' : 'Uncaught') . ' PHP Exception %s: "%s" at %s line %s';
        $this->logger->log($type, sprintf($message, get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine()), ['exception' => $exception]);
        if ($exception->getPrevious()) {
            $this->logException($exception->getPrevious(), $type, true);
        }
    }
}
