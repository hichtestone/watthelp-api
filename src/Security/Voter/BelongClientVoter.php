<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\HasClientInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class BelongClientVoter extends Voter
{
    public const BELONG_CLIENT = 'BELONG_CLIENT';

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, $subject): bool
    {
        return self::BELONG_CLIENT === $attribute && $subject instanceof HasClientInterface;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$subject instanceof HasClientInterface) {
            throw new \LogicException('If you want use BELONG_CLIENT restriction, you should implement HasClientInterface over entity.');
        }

        return $user->getClient()->getId() === $subject->getClient()->getId();
    }
}