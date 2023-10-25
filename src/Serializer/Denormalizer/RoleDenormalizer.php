<?php

declare(strict_types=1);

namespace App\Serializer\Denormalizer;

use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class RoleDenormalizer extends AbstractDenormalizer implements DenormalizerInterface
{
    /**
     * @inheritDoc
     * @throws \Doctrine\ORM\ORMException
     */
    public function denormalize($data, string $class, string $format = null, array $context = []): Role
    {
        $role = parent::denormalize($data, $class, $format, ['object_to_populate' => $context['object_to_populate'] ?? new Role()]);

        if (isset($data['users']) && is_array($data['users'])) {
            $users = [];
            foreach ($data['users'] as $user) {
                $users[] = $this->entityManager->getReference(User::class, $user);
            }
            $role->setUsers(new ArrayCollection($users));
        }

        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $permissions = [];
            foreach ($data['permissions'] as $permissionId) {
                $permissions[] = $this->entityManager->getReference(Permission::class, $permissionId);
            }
            $role->setPermissions(new ArrayCollection($permissions));
        }

        return $role;
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return isset($data) && $type === Role::class;
    }
}