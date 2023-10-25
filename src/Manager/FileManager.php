<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\File;
use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;

class FileManager
{
    protected FileRepository $repository;
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(File::class);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByCriteria(array $criteria): ?File
    {
        return $this->repository->getByCriteria($criteria);
    }

    public function insert(File $file): void
    {
        $this->entityManager->persist($file);
        $this->entityManager->flush();
    }

    public function delete(File $file): void
    {
        $this->entityManager->remove($file);
        $this->entityManager->flush();
    }

    public function update(File $file): void
    {
        $file->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($file);
        $this->entityManager->flush();
    }
}