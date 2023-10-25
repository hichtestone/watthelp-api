<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\User;
use App\Manager\PermissionManager;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserNormalizer implements ContextAwareNormalizerInterface
{
    private PermissionManager $permissionManager;
    private ObjectNormalizer $normalizer;

    public function __construct(
        PermissionManager $permissionManager,
        ObjectNormalizer $normalizer
    ) {
        $this->permissionManager = $permissionManager;
        $this->normalizer = $normalizer;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \Symfony\Component\Serializer\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Serializer\Exception\LogicException
     */
    public function normalize($user, string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($user, $format, $context);

        $expands = $context['groups'] ?? [];
        if (in_array(User::EXPAND_DATA_USER_PERMISSION_CODES, $expands, true)) {
            $data['permissions'] = $this->permissionManager->getPermissionsOfUser($user, true);
        }
        if (in_array(User::EXPAND_DATA_USER_PERMISSIONS, $expands, true)) {
            $permissions = $this->permissionManager->getPermissionsOfUser($user);
            foreach ($permissions as &$permission) {
                $permission = $this->normalizer->normalize($permission, $format, $context);
            }
            $data['permissions'] = $permissions;
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof User;
    }
}