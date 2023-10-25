<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;

class AuthenticationFailureListener
{

    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event): void
    {
        $exception = $event->getException();

        // Bad credential
        if ($exception instanceof BadCredentialsException) {
            $response = [
                'error' => 'Bad credentials',
                'messages' => ['Email ou mot de passe incorrect.'],
            ];

            $event->setResponse(new JsonResponse($response, 400));

            return;
        }

        // Access denied
        if ($exception->getPrevious() instanceof AccessDeniedException) {
            $event->setResponse(new JsonResponse([
                'error' => 'Access denied',
                'messages' => $exception->getPrevious()->getMessage(),
            ], 400));
        }
    }
}