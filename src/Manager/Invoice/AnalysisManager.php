<?php

declare(strict_types=1);

namespace App\Manager\Invoice;

use App\Entity\Client;
use App\Entity\Invoice\Analysis;
use App\OptionResolver\Invoice\Analysis\SearchOptions;
use App\Repository\Invoice\AnalysisRepository;
use App\Request\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class AnalysisManager
{
    private AnalysisRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Analysis::class);
    }

    public function insert(Analysis $analysis)
    {
        $this->entityManager->persist($analysis);
        $this->entityManager->flush();
    }

    public function update(Analysis $analysis)
    {
        $this->entityManager->persist($analysis);
        $this->entityManager->flush();
    }

    public function delete(Analysis $analysis)
    {
        $this->entityManager->remove($analysis);
        $this->entityManager->flush();
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    private function prepareFilters(array $filters): array
    {
        $resolver = new SearchOptions();
        $filters = $resolver->resolve($filters);

        return $filters;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @throws \InvalidArgumentException
     */
    public function findByFilters(Client $client, array $filters, ?Pagination $pagination = null): Paginator
    {
        $filters = $this->prepareFilters($filters);
        return $this->repository->findByFilters($client, $filters, $pagination);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     */
    public function getByCriteria(Client $client, array $criteria): ?Analysis
    {
        return $this->repository->getByCriteria($client, $criteria);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function deleteByFilters(Client $client, array $filters): void
    {
        $filters = $this->prepareFilters($filters);
        $this->repository->deleteByFilters($client, $filters);
    }
}