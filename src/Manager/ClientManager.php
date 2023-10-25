<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Request\Pagination;
use App\OptionResolver\User\SearchOptions;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ClientManager
{
    private ClientRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Client::class);
    }

    public function insert(Client $client): void
    {
        $this->entityManager->persist($client);
        $this->entityManager->flush();
    }

    public function update(Client $client): void
    {
        $client->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($client);
        $this->entityManager->flush();
    }

    public function delete(Client $client): void
    {
        $this->entityManager->remove($client);
        $this->entityManager->flush();
    }

    /**
     * @throws NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(array $criteria): ?Client
    {
        return $this->repository->getByCriteria($criteria);
    }
}