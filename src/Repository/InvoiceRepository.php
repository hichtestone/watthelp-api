<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Query\Criteria;
use App\Request\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Invoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invoice[]    findAll()
 * @method Invoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function buildBasicQuery(Client $client, QueryBuilder $builder, string $select = 'i'): QueryBuilder
    {
        $query = $builder->select($select)
            ->from(Invoice::class, 'i')
            ->where($builder->expr()->eq('i.client', ':client'))
            ->setParameter(':client', $client->getId());

        return $query;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     */
    public function hasInvoicesEmittedAfter(Client $client, \DateTimeInterface $threshold): bool
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $this->buildBasicQuery($client, $builder, 'COUNT(i.id) as count');
        $query->andWhere($builder->expr()->gt('i.emittedAt', ':threshold'))
            ->setParameter(':threshold', $threshold, Types::DATETIME_MUTABLE);

        $queryResult = $query->getQuery()->execute();

        return intval($queryResult[0]['count']) > 0;
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
        $query = $this->buildBasicQuery($client, $builder);

        // Filters
        if ($ids = $filters['id']) {
            $query->andWhere($builder->expr()->in('i.id', ':ids'))
                ->setParameter(':ids', $ids);
        }

        if (!empty($filters['exclude_ids'])) {
            $query->andWhere($builder->expr()->notIn('i.id', ':exclude_ids'))
                ->setParameter(':exclude_ids', $filters['exclude_ids']);
        }

        if ($reference = $filters['reference']) {
            $query->andWhere($builder->expr()->like('i.reference', ':reference'))
                ->setParameter(':reference', '%'.$reference.'%');
        }

        if ($references = $filters['references']) {
            $query->andWhere($builder->expr()->in('i.reference', ':references'))
                ->setParameter(':references', $references);            
        }

        if (!is_null($filters['has_analysis'])) {
            $query->leftJoin('i.analysis', 'a');
            if ($filters['has_analysis']) {
                $query->andWhere($builder->expr()->isNotNull('a'));
            } else {
                $query->andWhere($builder->expr()->isNull('a'));
            }
        }

        // Sort
        if (!empty($sort)) {
            $sortOrder = $pagination->getSortOrder();
            switch ($sort) {
                case 'id':
                    $query->orderBy('i.id', $sortOrder);
                    break;
                case 'reference':
                    $query->orderBy('i.reference', $sortOrder);
                    break;
                case 'amount_ht':
                    $query->orderBy('i.amountHT', $sortOrder);
                    break;
                case 'amount_tva':
                    $query->orderBy('i.amountTVA', $sortOrder);
                    break;
                case 'amount_ttc':
                    $query->orderBy('i.amountTTC', $sortOrder);
                    break;
                case 'emitted_at':
                    $query->orderBy('i.emittedAt', $sortOrder);
                    break;
            }

            if ('id' !== $sort) {
                $query->addOrderBy('i.id', 'asc');
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
    public function getByCriteria(Client $client, array $criteria): ?Invoice
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $this->buildBasicQuery($client, $builder);

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\Invoice\Id:
                    $query->andWhere($builder->expr()->eq('i.id', ':id'))
                        ->setParameter(':id', $where->getCriteria());
                    break;
                case $where instanceof Criteria\Invoice\Reference:
                    $query->andWhere($builder->expr()->eq('i.reference', ':reference'))
                        ->setParameter(':reference', $where->getCriteria());
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
    public function getCountInvoice(Client $client): array
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $this->buildBasicQuery($client, $builder, 'COUNT(i.id) as num');

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     */
    public function deleteByFilters(Client $client, array $filters): void
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->delete(Invoice::class, 'i')
            ->where($builder->expr()->eq('i.client', ':client'))
            ->setParameter(':client', $client->getId());

        if ($ids = $filters['ids']) {
            $query->andWhere($builder->expr()->in('i.id', ':ids'))
                ->setParameter(':ids', $ids);
        }
        if ($references = $filters['references']) {
            $query->andWhere($builder->expr()->in('i.reference', ':references'))
                ->setParameter(':references', $references);
        }

        $query->getQuery()->execute();
    }
}
