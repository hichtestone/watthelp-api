<?php

declare(strict_types=1);

namespace App\Repository\Budget;

use App\Entity\Budget\DeliveryPointBudget;
use App\Entity\DeliveryPoint;
use App\Query\Criteria;
use App\Repository\DeliveryPointRepository;
use App\Request\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeliveryPointBudget|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryPointBudget|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryPointBudget[]    findAll()
 * @method DeliveryPointBudget[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryPointBudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryPointBudget::class);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(array $criteria): ?DeliveryPointBudget
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('dpb')
            ->from(DeliveryPointBudget::class, 'dpb');

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\Budget\DeliveryPoint\Id:
                    $query->andWhere($builder->expr()->eq('dpb.id', ':id'))
                        ->setParameter(':id', $where->getCriteria());
                    break;
                case $where instanceof Criteria\Budget\DeliveryPointBudget\DeliveryPoint:
                    $query->andWhere($builder->expr()->eq('dpb.deliveryPoint', ':deliveryPoint'))
                        ->setParameter(':deliveryPoint', $where->getCriteria());
                    break;
                case $where instanceof Criteria\Budget\DeliveryPointBudget\Budget:
                    $query->andWhere($builder->expr()->eq('dpb.budget', ':budget'))
                        ->setParameter(':budget', $where->getCriteria());
                    break;
                default:
                    throw new \LogicException('Criteria invalid.');
                    break;
            }
        }

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function findByFilters(array $filters, ?Pagination $pagination = null): Paginator
    {
        $sort = null;
        if ($pagination) {
            $sort = $pagination->getSort();
        }

        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('dpb')
            ->from(DeliveryPointBudget::class, 'dpb')
            ->leftJoin('dpb.budget', 'b')
            ->leftJoin('dpb.deliveryPoint', 'd');

        if ($budget = $filters['budget']) {
            $query->andWhere($builder->expr()->eq('dpb.budget', $budget->getId()));
        }

        if ($deliveryPoint = $filters['delivery_point']) {
            $query->andWhere($builder->expr()->eq('dpb.deliveryPoint', $deliveryPoint->getId()));
        }

        if ($deliveryPoints = $filters['delivery_points']) {
            $query->andWhere($builder->expr()->in('dpb.deliveryPoint', array_map(fn (DeliveryPoint $dp) => $dp->getId(), $deliveryPoints)));
        }

        if ($year = $filters['year']) {
            $query->andWhere($builder->expr()->eq('b.year', $year));
        }

        $query = DeliveryPointRepository::addFilters($filters, $query, $builder);

        if ($filters['no_invoice_for_months']) {
            $query->groupBy('dpb.id');
        }

        if (!empty($sort)) {
            $sortOrder = $pagination->getSortOrder();
            switch ($sort) {
                case 'id':
                    $query->orderBy('dpb.id', $sortOrder);
                    break;
                case 'reference':
                    $query->orderBy('d.reference', $sortOrder);
                    break;
                case 'code':
                    $query->orderBy('d.code', $sortOrder);
                    break;
                case 'contract':
                    $query->orderBy('d.contract', $sortOrder);
                    break;
                case 'scope_date':
                    $query->orderBy('d.scopeDate', $sortOrder);
                    break;
            }

            if ('id' !== $sort) {
                $query->addOrderBy('dpb.id', 'asc');
            }
        }

        if ($pagination) {    
            $page = $pagination->getPage();
            $perPage = $pagination->getPerPage();
            if (!empty($perPage)) {
                $query->setMaxResults($perPage);
                if (!empty($page)) {
                    $query->setFirstResult(($page - 1) * $perPage);
                }
            }
        }

        $paginator = new Paginator($query, false);
        $paginator->setUseOutputWalkers(!is_null($filters['no_invoice_for_months']));

        return $paginator;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     */
    public function deleteByFilters(array $filters): void
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->delete(DeliveryPointBudget::class, 'dpb');

        if ($budget = $filters['budget']) {
            $query->andWhere($builder->expr()->eq('dpb.budget', ':budget'))
                ->setParameter(':budget', $budget->getId());
        }
        if ($ids = $filters['ids']) {
            if ($ids !== '*') {
                $query->andWhere($builder->expr()->in('dpb.id', ':ids'))
                    ->setParameter(':ids', $ids);
            }
        }

        $query->getQuery()->execute();
    }
}