<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Budget;
use App\Entity\Client;
use App\Query\Criteria;
use App\Request\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Budget|null find($id, $lockMode = null, $lockVersion = null)
 * @method Budget|null findOneBy(array $criteria, array $orderBy = null)
 * @method Budget[]    findAll()
 * @method Budget[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Budget::class);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(Client $client, array $criteria): ?Budget
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('b')
            ->from(Budget::class, 'b')
            ->where($builder->expr()->eq('b.client', ':client'))
            ->setParameter(':client', $client->getId());

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\Budget\Id:
                    $query->andWhere($builder->expr()->eq('b.id', ':id'))
                        ->setParameter(':id', $where->getCriteria());
                    break;
                case $where instanceof Criteria\Budget\Year:
                    $query->andWhere($builder->expr()->eq('b.year', ':year'))
                        ->setParameter(':year', $where->getCriteria());
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
    public function findByFilters(Client $client, array $filters, ?Pagination $pagination = null): Paginator
    {
        $sort = null;
        if ($pagination) {
            $sort = $pagination->getSort();
        }
        
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('b')
            ->from(Budget::class, 'b')
            ->where($builder->expr()->eq('b.client', $client->getId()));

        if ($ids = $filters['ids']) {
            $query->andWhere($builder->expr()->in('b.id', ':ids'))
                ->setParameter(':ids', $ids);
        }

        if ($year = $filters['year']) {
            $query->andWhere($builder->expr()->eq('b.year', $year));
        }

        if ($years = $filters['years']) {
            $query->andWhere($builder->expr()->in('b.year', $years));
        }

        if ($maxYear = $filters['max_year']) {
            $query->andWhere($builder->expr()->lte('b.year', $maxYear));
        }

        if (!empty($sort)) {
            $sortOrder = $pagination->getSortOrder();
            switch ($sort) {
                case 'id':
                    $query->orderBy('b.id', $sortOrder);
                    break;
                case 'year':
                    $query->orderBy('b.year', $sortOrder);
                    break;
                case 'average_price':
                    $query->orderBy('b.averagePrice', $sortOrder);
                    break;
                case 'total_hours':
                    $query->orderBy('b.totalHours', $sortOrder);
                    break;
                case 'total_consumption':
                    $query->orderBy('b.totalConsumption', $sortOrder);
                    break;
                case 'total_amount':
                    $query->orderBy('b.totalAmount', $sortOrder);
                    break;                
            }

            if ('id' !== $sort) {
                $query->addOrderBy('b.id', 'asc');
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
        $paginator->setUseOutputWalkers(false);

        return $paginator;
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
    public function deleteByFilters(Client $client, array $filters): void
    {
        // find budgets with filters provided
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('b.id')
            ->from(Budget::class, 'b')
            ->where($builder->expr()->eq('b.client', ':client'))
            ->setParameter(':client', $client->getId());

        if ($ids = $filters['ids']) {
            if ($ids !== '*') {
                $query->andWhere($builder->expr()->in('b.id', ':ids'))
                    ->setParameter(':ids', $ids);
            }
        }
        $budgetsFound = $query->getQuery()->getResult();

        if (!empty($budgetsFound)) {
            $budgetIds = array_map(fn(array $b) => $b['id'], $budgetsFound);

            // delete the budgets
            $builder = $this->getEntityManager()->createQueryBuilder();
            $deleteQuery = $builder->delete(Budget::class, 'b')
                ->where($builder->expr()->in('b.id', ':ids'))
                ->setParameter(':ids', $budgetIds);
            $deleteQuery->getQuery()->execute();
        }
    }
}
