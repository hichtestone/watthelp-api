<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\User;
use App\OptionResolver\Permission\SearchOptions;
use App\Repository\PermissionRepository;
use App\Request\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PermissionManager
{
    private PermissionRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Permission::class);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(array $criteria): ?Permission
    {
        return $this->repository->getByCriteria($criteria);
    }

    public function getCodesByRoles(array $roles): array
    {
        return $this->repository->getCodesByRoles($roles);
    }

    public function getByRoles(array $roles): array
    {
        return $this->repository->getByRoles($roles);
    }

    public function getPermissionsOfUser(User $user, bool $onlyCodes = false): array
    {
        if ($user->isSuperAdmin()) {
            $permissions = iterator_to_array($this->findByFilters([]));
            return $onlyCodes ? array_map(fn($p) => $p->getCode(), $permissions) : $permissions;
        }

        $roles = array_map(fn (Role $role) => $role->getId(), $user->getUserRoles()->getValues());
        return $onlyCodes ? $this->getCodesByRoles($roles) : $this->getByRoles($roles);
    }

    public function hasPermissions(User $user, array $permissions): bool
    {
        return $user->isSuperAdmin() || $this->repository->hasPermissions($user, $permissions);
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function findByFilters(array $filters, ?Pagination $pagination = null): Paginator
    {
        $resolver = new SearchOptions();
        $filters = $resolver->resolve($filters);

        return $this->repository->findByFilters($filters, $pagination);
    }
}