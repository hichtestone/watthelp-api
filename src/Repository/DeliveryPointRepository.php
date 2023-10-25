<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\DeliveryPoint;
use App\Query\Criteria;
use App\Request\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeliveryPoint|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryPoint|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryPoint[]    findAll()
 * @method DeliveryPoint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryPointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryPoint::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(Client $client, array $criteria): ?DeliveryPoint
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('d')
            ->from(DeliveryPoint::class, 'd')
            ->where($builder->expr()->eq('d.client', ':client'))
            ->setParameter(':client', $client->getId());

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\DeliveryPoint\Id:
                    $query->andWhere($builder->expr()->eq('d.id', ':id'))
                        ->setParameter(':id', $where->getCriteria());
                    break;
                case $where instanceof Criteria\DeliveryPoint\Reference:
                    $query->andWhere($builder->expr()->eq('d.reference', ':reference'))
                        ->setParameter(':reference', $where->getCriteria());
                    break;
                case $where instanceof Criteria\DeliveryPoint\Code:
                    $query->andWhere($builder->expr()->eq('d.code', ':code'))
                        ->setParameter(':code', $where->getCriteria());
                break;

                default:
                    throw new \LogicException('Criteria invalid.');
                    break;
            }
        }

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * Search a collection by filters.
     *
     * @throws \Exception
     */
    public function findByFilters(Client $client, array $filters, ?Pagination $pagination = null): Paginator
    {
        $sort = null;
        if ($pagination) {
            $sort = $pagination->getSort();
        }

        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('d')
            ->from(DeliveryPoint::class, 'd')
            ->where($builder->expr()->eq('d.client', ':client'))
            ->setParameter(':client', $client->getId());

        if ($ids = $filters['ids']) {
            $query->andWhere($builder->expr()->in('d.id', ':ids'))
                ->setParameter(':ids', $ids);
        }

        if (!empty($filters['exclude_ids'])) {
            $query->andWhere($builder->expr()->notIn('d.id', ':exclude_ids'))
                ->setParameter(':exclude_ids', $filters['exclude_ids']);
        }

        $query = self::addFilters($filters, $query, $builder);

        if (!empty($sort)) {
            $sortOrder = $pagination->getSortOrder();
            switch ($sort) {
                case 'id':
                    $query->orderBy('d.id', $sortOrder);
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
                $query->addOrderBy('d.id', 'asc');
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

    public static function addFilters(array $filters, QueryBuilder $query, QueryBuilder $builder): QueryBuilder
    {
        if ($reference = $filters['reference']) {
            $query->andWhere($builder->expr()->like('d.reference', ':reference'))
                ->setParameter(':reference', '%'.$reference.'%');
        }

        if ($references = $filters['references']) {
            $query->andWhere($builder->expr()->in('d.reference', ':references'))
                ->setParameter(':references', $references);            
        }

        if ($code = $filters['code']) {
            $query->andWhere($builder->expr()->like('d.code', ':code'))
                ->setParameter(':code', '%'.$code.'%');
        }

        if ($contract = $filters['contract']) {
            $query->andWhere($builder->expr()->eq('d.contract', ':contract'))
                ->setParameter(':contract', $contract);
        }

        if (!is_null($filters['is_in_scope'])) {
            $query->andWhere($builder->expr()->eq('d.isInScope', ':isInScope'))
                ->setParameter(':isInScope', $filters['is_in_scope']);
        }

        // Get the delivery points that don't have any invoice for X months
        if ($numberOfMonths = $filters['no_invoice_for_months']) {
            $limitInvoiceDate = (new \DateTime())->sub(new \DateInterval("P{$numberOfMonths}M"));
            $query->leftJoin('d.deliveryPointInvoices', 'dpi')
                ->leftJoin('dpi.invoice', 'i')
                ->groupBy('d.id')
                ->having($builder->expr()->lt($builder->expr()->max('i.emittedAt'), ':limitInvoiceDate'))
                ->setParameter(':limitInvoiceDate', $limitInvoiceDate);
        }

        return $query;
    }

    public function getMapInfo(Client $client): array
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('d.id, d.address, d.longitude, d.latitude, d.isInScope AS is_in_scope')
            ->from(DeliveryPoint::class, 'd')
            ->andWhere($builder->expr()->eq('d.client', ':client'))
            ->setParameter(':client', $client->getId());

        return $query->getQuery()->getResult();
    }

    public function getCountDeliveryPoints(Client $client): array
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder
            ->select('SUM(CASE WHEN d.isInScope = 1 THEN 1 ELSE 0 END) as delivery_points_in_scope')
            ->addSelect('SUM(CASE WHEN d.isInScope = 0 THEN 1 ELSE 0 END) as delivery_points_not_in_scope')
            ->from(DeliveryPoint::class, 'd');

        if (isset($client)) {
            $query->andWhere($builder->expr()->eq('d.client', $client->getId()));
        }

        return $query->getQuery()->getArrayResult();
    }
}
