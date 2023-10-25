<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Client;
use App\Entity\Tax;
use App\OptionResolver\Tax\SearchOptions;
use App\Repository\TaxRepository;
use App\Request\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TaxManager
{
    private TaxRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Tax::class);
    }

    public function insert(Tax $tax): void
    {
        $this->entityManager->persist($tax);
        $this->entityManager->flush();
    }

    public function update(Tax $tax): void
    {
        $this->entityManager->persist($tax);
        $this->entityManager->flush();
    }

    public function delete(Tax $tax): void
    {
        $this->entityManager->remove($tax);
        $this->entityManager->flush();
    }

    /**
     * @throws NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(Client $client, array $criteria): ?Tax
    {
        return $this->repository->getByCriteria($client, $criteria);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function findByFilters(Client $client, array $filters, ?Pagination $pagination = null): Paginator
    {
        $resolver = new SearchOptions();
        $filters = $resolver->resolve($filters);

        return $this->repository->findByFilters($client, $filters, $pagination);
    }
}