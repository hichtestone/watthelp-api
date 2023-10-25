<?php

declare(strict_types=1);

namespace App\Validator\Constraint\Notification;

use App\Manager\NotificationManager;
use App\Query\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\RuntimeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class SelectableValidator extends ConstraintValidator
{
    private TokenStorageInterface $tokenStorage;
    private NotificationManager $notificationManager;

    public function __construct(NotificationManager $notificationManager, TokenStorageInterface $tokenStorage)
    {
        $this->notificationManager = $notificationManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     *
     * @throws NonUniqueResultException
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token || !($authenticatedUser = $token->getUser())) {
            throw new RuntimeException('User must be connected');
        }

        $criteria = [
            new Criteria\Notification\Id($value),
            new Criteria\User\Id($authenticatedUser->getId()),
        ];

        $notification = $this->notificationManager->getByCriteria($criteria);
        if (!$notification) {
            $this->context->buildViolation($constraint->notExistingNotification)->addViolation();

            return;
        }
    }
}
