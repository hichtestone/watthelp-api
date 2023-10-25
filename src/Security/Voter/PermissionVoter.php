<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Permission;
use App\Manager\PermissionManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PermissionVoter extends Voter
{
    private PermissionManager $permissionManager;
    private RequestStack $requestStack;

    public function __construct(
        PermissionManager $permissionManager,
        RequestStack $requestStack
    ) {
        $this->permissionManager = $permissionManager;
        $this->requestStack = $requestStack;
    }

    /**
     * @inheritDoc
     */
    protected function supports($attribute, $subject): bool
    {
        $permissions = is_string($attribute) ? [$attribute] : $attribute;
        return is_array($permissions) && count($permissions) === count(array_intersect($permissions, Permission::AVAILABLE_PERMISSION_CODES));
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        $permissions = is_string($attribute) ? [$attribute] : $attribute;
        $hasPermissions = $this->permissionManager->hasPermissions($user, $permissions);

        // if the user doesn't have the permission to view an entity, don't return an error
        // but change the response so that it only contains the "restricted" group
        if (!$hasPermissions && count($permissions) === 1 &&
            substr_compare($permissions[0], '.view', -strlen('.view')) === 0) {
            if ($request = $this->requestStack->getCurrentRequest()) {
                $request->attributes->set('_restricted_group', 'restricted');
            }
            return true;
        }
        return $hasPermissions;
    }
}