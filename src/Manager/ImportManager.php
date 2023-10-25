<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Import;
use Doctrine\ORM\EntityManagerInterface;

class ImportManager
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Import::class);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByCriteria(array $criteria): ?Import
    {
        return $this->repository->getByCriteria($criteria);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function insert(Import $data): void
    {
        $this->entityManager->persist($data);
        $this->entityManager->flush();
    }
}