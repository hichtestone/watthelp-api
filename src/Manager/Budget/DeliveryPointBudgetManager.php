<?php

declare(strict_types=1);

namespace App\Manager\Budget;

use App\Entity\Budget;
use App\Entity\Budget\DeliveryPointBudget;
use App\Entity\Contract;
use App\Entity\DeliveryPoint;
use App\Manager\BudgetManager;
use App\OptionResolver\Budget\DeliveryPoint\DeleteOptions;
use App\OptionResolver\Budget\DeliveryPoint\SearchOptions;
use App\Query\Criteria;
use App\Repository\Budget\DeliveryPointBudgetRepository;
use App\Request\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DeliveryPointBudgetManager
{
    private DeliveryPointBudgetRepository $repository;
    private EntityManagerInterface $entityManager;
    private BudgetManager $budgetManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        BudgetManager $budgetManager
    ) {
        $this->entityManager = $entityManager;
        $this->budgetManager = $budgetManager;
        $this->repository = $entityManager->getRepository(DeliveryPointBudget::class);
    }

    public function update(DeliveryPointBudget $deliveryPointBudget): void
    {
        $deliveryPointBudget->setUpdatedAt(new \DateTime());
        $this->entityManager->persist($deliveryPointBudget);
        $this->entityManager->flush();
    }

    public function delete(DeliveryPointBudget $deliveryPointBudget): void
    {
        $this->entityManager->remove($deliveryPointBudget);
        $this->entityManager->flush();
    }

    public function getByCriteria(array $criteria): ?DeliveryPointBudget
    {
        return $this->repository->getByCriteria($criteria);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function findByFilters(array $filters, ?Pagination $pagination = null): Paginator
    {
        if (isset($filters['year']) && !is_int($filters['year'])) {
            $filters['year'] = intval($filters['year']);
        }

        if (isset($filters['budget']) && !$filters['budget'] instanceof Budget) {
            $filters['budget'] = $this->entityManager->getReference(Budget::class, $filters['budget']);
        }

        if (isset($filters['delivery_point']) && !$filters['delivery_point'] instanceof DeliveryPoint) {
            $filters['delivery_point'] = $this->entityManager->getReference(DeliveryPoint::class, $filters['delivery_point']);
        }

        if (isset($filters['delivery_points'])) {
            foreach ($filters['delivery_points'] as &$dp) {
                if (!$dp instanceof DeliveryPoint) {
                    $dp = $this->entityManager->getReference(DeliveryPoint::class, $dp);
                }
            }
        }

        if (isset($filters['contract']) && !$filters['contract'] instanceof Contract) {
            $filters['contract'] = $this->entityManager->getReference(Contract::class, $filters['contract']);
        }

        if (isset($filters['is_in_scope'])) {
            $filters['is_in_scope'] = boolval($filters['is_in_scope']);
        }

        $resolver = new SearchOptions();
        $filters = $resolver->resolve($filters);

        return $this->repository->findByFilters($filters, $pagination);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function deleteByFilters(array $filters): void
    {
        if (isset($filters['budget']) && !$filters['budget'] instanceof Budget) {
            $filters['budget'] = $this->entityManager->getReference(Budget::class, $filters['budget']);
        }

        $resolver = new DeleteOptions();
        $filters = $resolver->resolve($filters);

        $this->repository->deleteByFilters($filters);
    }

    public function count(array $criteria): int
    {
        return $this->repository->count($criteria);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getPrevious(DeliveryPointBudget $dpb, ?Budget $previousBudget = null): ?DeliveryPointBudget
    {
        $previousBudget ??= $this->budgetManager->getPrevious($dpb->getBudget());
        if (!$previousBudget) {
            return null;
        }

        return $this->getByCriteria([
            new Criteria\Budget\DeliveryPointBudget\Budget($previousBudget->getId()),
            new Criteria\Budget\DeliveryPointBudget\DeliveryPoint($dpb->getDeliveryPoint()->getId())
        ]);
    }
}