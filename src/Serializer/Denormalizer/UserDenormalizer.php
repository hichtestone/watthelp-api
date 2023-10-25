<?php

declare(strict_types=1);

namespace App\Serializer\Denormalizer;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UserDenormalizer extends AbstractDenormalizer implements DenormalizerInterface
{
    private UserPasswordEncoderInterface $encoder;

    public function __construct(
        UserPasswordEncoderInterface $encoder,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($entityManager);
        $this->encoder = $encoder;
    }

    /**
     * Denormalizes data back into an object of the given class.
     *
     * @param mixed  $data    Data to restore
     * @param string $class   The expected class to instantiate
     * @param string $format  Format the given data was extracted from
     * @param array  $context Options available to the denormalizer
     *
     * @throws ORMException
     */
    public function denormalize($data, string $class, string $format = null, array $context = []): User
    {
        $user = parent::denormalize($data, $class, $format, ['object_to_populate' => $context['object_to_populate'] ?? new User()]);

        if (isset($data['password'])) {
            $user->setPassword($this->encoder->encodePassword($user, $data['password']));
        }

        if (isset($data['roles']) && is_array($data['roles'])) {
            $roles = new ArrayCollection();
            foreach ($data['roles'] as $roleId) {
                $roles->add($this->entityManager->getReference(Role::class, $roleId));
            }
            $user->setUserRoles($roles);
        }

        $this->handleFile('avatar');

        return $user;
    }

    /**
     * Checks whether the given class is supported for denormalization by this normalizer.
     *
     * @param mixed  $data   Data to denormalize from
     * @param string $type   The class to which the data should be denormalized
     * @param string $format The format being deserialized from
     *
     * @return bool
     */
    public function supportsDenormalization($data, string $type, ?string $format = null): bool
    {
        return isset($data) && User::class === $type;
    }
}