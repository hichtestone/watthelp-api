<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Client;
use App\Entity\Role;
use App\Entity\User;
use App\OptionResolver\Role\DeleteOptions;
use App\OptionResolver\Role\SearchOptions;
use App\Repository\RoleRepository;
use App\Request\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;

class RoleManager
{
    private RoleRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Role::class);
    }

    public function insert(Role $role): void
    {
        $this->entityManager->persist($role);
        $this->entityManager->flush();
    }

    public function update(Role $role): void
    {
        $role->setUpdatedAt(new \DateTime());
        $this->entityManager->persist($role);
        $this->entityManager->flush();
    }

    public function delete(Role $role): void
    {
        $this->entityManager->remove($role);
        $this->entityManager->flush();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(Client $client, array $criteria): ?Role
    {
        return $this->repository->getByCriteria($client, $criteria);
    }

    /**
     * @throws ORMException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function findByFilters(Client $client, array $filters, ?Pagination $pagination = null): Paginator
    {
        if (isset($filters['users'])) {
            if (!is_array($filters['users'])) {
                throw new \LogicException('The "users" filter in Role must be an array of user ids');
            }
            foreach ($filters['users'] as &$user) {
                if (!$user instanceof User) {
                    $user = $this->entityManager->getReference(User::class, $user);
                }
            }
        }

        $resolver = new SearchOptions();
        $filters = $resolver->resolve($filters);

        return $this->repository->findByFilters($client, $filters, $pagination);
    }

    public function count(array $criteria): int
    {
        return $this->repository->count($criteria);
    }
}