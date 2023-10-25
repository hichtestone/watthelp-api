<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Contract;
use App\Query\Criteria;
use App\Request\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Contract|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contract|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contract[]    findAll()
 * @method Contract[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contract::class);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function buildBasicQuery(Client $client, QueryBuilder $builder, string $select = 'c'): QueryBuilder
    {
        $query = $builder->select($select)
            ->from(Contract::class, 'c')
            ->where($builder->expr()->eq('c.client', ':client'))
            ->setParameter(':client', $client->getId());

        return $query;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     */
    public function getMinimumInvoicePeriod(Client $client): ?string
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $this->buildBasicQuery($client, $builder, 'MIN(c.invoice_period) AS minInvoicePeriod');
        $query->andWhere($builder->expr()->lte('c.startedAt', ':now'))
            ->andWhere($builder->expr()->orX(
                $builder->expr()->gte('c.finishedAt', ':now'),
                $builder->expr()->isNull('c.finishedAt')
            ))
            ->setParameter(':now', new \DateTime(), Types::DATETIME_MUTABLE);

        $queryResult = $query->getQuery()->execute();

        return $queryResult[0]['minInvoicePeriod'];
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
            $query->andWhere($builder->expr()->in('c.id', ':ids'))
                ->setParameter(':ids', $ids);
        }

        if (!empty($filters['exclude_ids'])) {
            $query->andWhere($builder->expr()->notIn('c.id', ':exclude_ids'))
                ->setParameter(':exclude_ids', $filters['exclude_ids']);
        }

        if ($reference = $filters['reference']) {
            $query->andWhere($builder->expr()->like('c.reference', ':reference'))
                ->setParameter(':reference', '%' . $reference . '%');
        }

        if ($references = $filters['references']) {
            $query->andWhere($builder->expr()->in('c.reference', ':references'))
                ->setParameter(':references', $references);            
        }

        if ($provider = $filters['provider']) {
            $query->andWhere($builder->expr()->eq('c.provider', ':provider'))
                ->setParameter(':provider', $provider);
        }

        if ($type = $filters['type']) {
            $query->andWhere($builder->expr()->eq('c.type', ':type'))
                ->setParameter(':type', $type);
        }

        if ($invoice_period = $filters['invoice_period']) {
            $query->andWhere($builder->expr()->eq('c.invoice_period', ':invoice_period'))
                ->setParameter(':invoice_period', $invoice_period);
        }

        // Sort
        if (!empty($sort)) {
            $sortOrder = $pagination->getSortOrder();
            switch ($sort) {
                case 'id':
                    $query->orderBy('c.id', $sortOrder);
                    break;
                case 'reference':
                    $query->orderBy('c.reference', $sortOrder);
                    break;
                case 'started_at':
                    $query->orderBy('c.startedAt', $sortOrder);
                    break;
                case 'finished_at':
                    $query->orderBy('c.finishedAt', $sortOrder);
                    break;
                case 'provider':
                    $query->orderBy('c.provider', $sortOrder);
                    break;
                case 'type':
                    $query->orderBy('c.type', $sortOrder);
                    break;
                case 'invoice_period':
                    $query->orderBy('c.invoice_period', $sortOrder);
                    break;
            }

            if ('id' !== $sort) {
                $query->addOrderBy('c.id', 'asc');
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
    public function getByCriteria(Client $client, array $criteria): ?Contract
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $this->buildBasicQuery($client, $builder);

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\Contract\Id:
                    $query->andWhere($builder->expr()->eq('c.id', ':id'))
                        ->setParameter(':id', $where->getCriteria());
                    break;
                default:
                    throw new \LogicException('Criteria invalid.');
                    break;
            }
        }

        return $query->getQuery()->getOneOrNullResult();
    }
}