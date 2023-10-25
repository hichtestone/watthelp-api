<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Gedmo\Translatable\TranslatableListener;
use Stof\DoctrineExtensionsBundle\EventListener\LocaleListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserLocaleSubscriber extends LocaleListener implements EventSubscriberInterface
{
    private TranslatorInterface $translator;
    private TranslatableListener $translatableListener;

    public function __construct(TranslatorInterface $translator, TranslatableListener $translatableListener)
    {
        $this->translator = $translator;
        $this->translatableListener = $translatableListener;
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $this->translator->setLocale($user->getLanguage());
    }

    public function onKernelRequest(RequestEvent $event)
    {
        // override LocaleListener, we don't want the locale of Translatable to depend on the user connected, this locale should always be french
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }
}
