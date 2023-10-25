<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Permission;
use App\Entity\User;
use App\Manager\PermissionManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SameUserOrHasUserPermissionVoter extends Voter
{
    public const SAME_USER_OR_HAS_EDIT_PERMISSION = 'SAME_USER_OR_HAS_EDIT_PERMISSION';
    public const SAME_USER_OR_HAS_VIEW_PERMISSION = 'SAME_USER_OR_HAS_VIEW_PERMISSION';

    private PermissionManager $permissionManager;

    public function __construct(PermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof User && 
            ($attribute === self::SAME_USER_OR_HAS_VIEW_PERMISSION ||
            $attribute === self::SAME_USER_OR_HAS_EDIT_PERMISSION);
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, $user, TokenInterface $token): bool
    {
        $connectedUser = $token->getUser();
        $permissionNeeded = $attribute === self::SAME_USER_OR_HAS_VIEW_PERMISSION ? Permission::USER_VIEW : Permission::USER_EDIT;

        return $user->getId() === $connectedUser->getId()
            || $this->permissionManager->hasPermissions($connectedUser, [$permissionNeeded]);
    }
}