<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Budget;
use App\Entity\Client;
use App\Query\Criteria;
use App\OptionResolver\Budget\SearchOptions;
use App\OptionResolver\Budget\DeleteOptions;
use App\Repository\BudgetRepository;
use App\Request\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;

class BudgetManager
{
    private BudgetRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Budget::class);
    }

    public function insert(Budget $budget): void
    {
        $this->entityManager->persist($budget);
        $this->entityManager->flush();
    }

    public function update(Budget $budget): void
    {
        $budget->setUpdatedAt(new \DateTime());
        $this->entityManager->persist($budget);
        $this->entityManager->flush();
    }

    public function delete(Budget $budget): void
    {
        $this->entityManager->remove($budget);
        $this->entityManager->flush();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(Client $client, array $criteria): ?Budget
    {
        return $this->repository->getByCriteria($client, $criteria);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getPrevious(Budget $budget): ?Budget
    {
        return $this->getByCriteria(
            $budget->getClient(),
            [new Criteria\Budget\Year($budget->getYear()-1)]
        );
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
        if (isset($filters['year']) && !is_int($filters['year'])) {
            $filters['year'] = intval($filters['year']);
        }

        if (isset($filters['max_year']) && !is_int($filters['max_year'])) {
            $filters['max_year'] = intval($filters['max_year']);
        }

        $resolver = new SearchOptions();
        $filters = $resolver->resolve($filters);

        return $this->repository->findByFilters($client, $filters, $pagination);
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
    public function deleteByFilters(Client $client, array $filters): void
    {
        $resolver = new DeleteOptions();
        $filters = $resolver->resolve($filters);

        $this->repository->deleteByFilters($client, $filters);
    }

    public function count(array $criteria): int
    {
        return $this->repository->count($criteria);
    }
}