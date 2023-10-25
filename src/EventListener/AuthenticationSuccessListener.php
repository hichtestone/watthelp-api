<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use App\Manager\UserManager;
use App\Service\RequestService;
use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AuthenticationSuccessListener
{
    private SerializerInterface $serializer;
    private RequestService $requestService;
    private UserManager $userManager;
    private UserService $userService;

    public function __construct(
        SerializerInterface $serializer,
        RequestService $requestService,
        UserManager $userManager,
        UserService $userService
    ) {
        $this->serializer = $serializer;
        $this->requestService = $requestService;
        $this->userManager = $userManager;
        $this->userService = $userService;
    }

    /**
     * @throws ExceptionInterface
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        $data = $event->getData();
        /** @var User $user */
        $user = $event->getUser();

        if ($user instanceof User) {
            $user->setConnectedAt(new \DateTime());
            $this->userManager->update($user);
            $data['client'] = $this->serializer->normalize($user->getClient(), null, ['groups' => 'default']);

            if ($this->userService->shouldSendImportReminder($user)) {
                $this->userService->sendImportReminder($user);
            }
        }

        $groups = array_merge(['default'], $this->requestService->getExpandData());
        $data['user'] = $this->serializer->normalize($user, null, compact('groups'));

        $event->setData($data);
    }
}