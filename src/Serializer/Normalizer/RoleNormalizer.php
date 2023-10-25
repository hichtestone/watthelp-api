<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Role;
use App\Manager\PermissionManager;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class RoleNormalizer implements ContextAwareNormalizerInterface
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
    public function normalize($role, $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($role, $format, $context);

        $expands = $context['groups'] ?? [];
        if (in_array(Role::EXPAND_DATA_PERMISSION_CODES, $expands)) {
            $data['permissions'] = $this->permissionManager->getCodesByRoles([$role->getId()]);
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof Role;
    }
}