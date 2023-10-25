<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\HasUserInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class BelongUserVoter extends Voter
{
    public const BELONG_USER = 'BELONG_USER';

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, $subject): bool
    {
        return self::BELONG_USER === $attribute && $subject instanceof HasUserInterface;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$subject instanceof HasUserInterface) {
            throw new \LogicException('If you want to use BELONG_USER restriction, you should implement HasUserInterface over entity.');
        }

        return $user->getId() === $subject->getUser()->getId();
    }
}