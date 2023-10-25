<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\DeliveryPoint;
use App\Entity\Pricing;
use App\Query\Criteria;
use App\Request\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Pricing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pricing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pricing[]    findAll()
 * @method Pricing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PricingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pricing::class);
    }

    /**
     * Search a collection by filters.
     * @throws \InvalidArgumentException
     */
    public function findByFilters(Client $client, array $filters, ?Pagination $pagination = null): Paginator
    {
        $sort = null;
        if ($pagination) {
            $sort = $pagination->getSort();
        }

        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('p')
            ->from(Pricing::class, 'p')
            ->where($builder->expr()->eq('p.client', ':client'))
            ->setParameter(':client', $client->getId());

        // Filters
        if ($ids = $filters['id']) {
            $query->andWhere($builder->expr()->in('p.id', ':ids'))
                ->setParameter(':ids', $ids);
        }

        if (!empty($filters['exclude_ids'])) {
            $query->andWhere($builder->expr()->notIn('p.id', ':exclude_ids'))
                ->setParameter(':exclude_ids', $filters['exclude_ids']);
        }

        if ($name = $filters['name']) {
            $query->andWhere($builder->expr()->like('p.name', ':name'))
                ->setParameter(':name', '%'.$name.'%');
        }

        if ($type = $filters['type']) {
            $query->andWhere($builder->expr()->like('p.type', ':type'))
                ->setParameter(':type', '%'.$type.'%');
        }

        if (isset($filters['enabled'])) {
            $now = new \DateTime();
            if ($filters['enabled']) {
                $query->andWhere($builder->expr()->lte('p.startedAt', ':now'));
                $query->andWhere($builder->expr()->orX(
                    $builder->expr()->gte('p.finishedAt', ':now'),
                    $builder->expr()->isNull('p.finishedAt')
                ));
            } else {
                $query->andWhere($builder->expr()->orX(
                    $builder->expr()->gt('p.startedAt', ':now'),
                    $builder->expr()->andX(
                        $builder->expr()->lt('p.finishedAt', ':now'),
                        $builder->expr()->isNotNull('p.finishedAt')
                    )
                ));
            }
            $query->setParameter(':now', $now, Type::DATETIME);
        }

        if (!empty($filters['excluded_periods'])) {
            foreach ($filters['excluded_periods'] as $period) {
                if (!empty($period['finished_at'])) {
                    $query->andWhere($builder->expr()->orX()->addMultiple([
                        $builder->expr()->andX(
                            $builder->expr()->gt('p.startedAt', $builder->expr()->literal($period['finished_at'])),
                            $builder->expr()->gt('p.finishedAt', $builder->expr()->literal($period['finished_at']))
                        ),
                        $builder->expr()->andX(
                            $builder->expr()->gt('p.startedAt', $builder->expr()->literal($period['finished_at'])),
                            $builder->expr()->isNull('p.finishedAt')
                        ),
                        $builder->expr()->andX(
                            $builder->expr()->lt('p.startedAt', $builder->expr()->literal($period['started_at'])),
                            $builder->expr()->lt('p.finishedAt', $builder->expr()->literal($period['started_at']))
                        )
                    ]));
                } else {
                    $query->andWhere($builder->expr()->andX(
                            $builder->expr()->lt('p.startedAt', $builder->expr()->literal($period['started_at'])),
                            $builder->expr()->lt('p.finishedAt', $builder->expr()->literal($period['started_at']))
                    ));
                }
            }
        }

        // Sort
        if (!empty($sort)) {
            $sortOrder = $pagination->getSortOrder();
            switch ($sort) {
                case 'id':
                    $query->orderBy('p.id', $sortOrder);
                    break;
                case 'name':
                    $query->orderBy('p.name', $sortOrder);
                    break;
                case 'type':
                    $query->orderBy('p.type', $sortOrder);
                    break;
                case 'started_at':
                    $query->orderBy('p.startedAt', $sortOrder);
                    break;
                case 'finished_at':
                    $query->orderBy('p.finishedAt', $sortOrder);
                    break;
            }

            if ('id' !== $sort) {
                $query->addOrderBy('p.id', 'asc');
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
     * @throws NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(Client $client, array $criteria): ?Pricing
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('p')
            ->from(Pricing::class, 'p')
            ->where($builder->expr()->eq('p.client', ':client'))
            ->setParameter(':client', $client->getId());

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\Pricing\Id:
                    $query->andWhere($builder->expr()->eq('p.id', ':id'))
                        ->setParameter(':id', $where->getCriteria());
                    break;
                default:
                    throw new \LogicException('Criteria invalid.');
                    break;
            }
        }

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @return Pricing[]
     * @throws \InvalidArgumentException
     */
    public function getPricingsBetweenInterval(DeliveryPoint $deliveryPoint, \DateTimeInterface $startedAt, \DateTimeInterface $finishedAt): array
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('p')
            ->from(Pricing::class, 'p')
            ->leftJoin('p.contracts', 'c')
            ->leftJoin('c.deliveryPoints', 'dp');

        $query->where($builder->expr()->eq('dp.id', ':deliveryPoint'))
            ->setParameter(':deliveryPoint', $deliveryPoint->getId());

        $query->andWhere($builder->expr()->lte('p.startedAt', ':finishedAt'))
            ->setParameter(':finishedAt', $finishedAt, Type::DATETIME);

        $query->andWhere($builder->expr()->orX(
            $builder->expr()->gte('p.finishedAt', ':startedAt'),
            $builder->expr()->isNull('p.finishedAt')
        ))->setParameter(':startedAt', $startedAt, Type::DATETIME);

        return $query->getQuery()->getResult();
    }
}