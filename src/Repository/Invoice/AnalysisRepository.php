<?php

declare(strict_types=1);

namespace App\Repository\Invoice;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\Invoice\Analysis;
use App\Query\Criteria;
use App\Request\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Analysis|null find($id, $lockMode = null, $lockVersion = null)
 * @method Analysis|null findOneBy(array $criteria, array $orderBy = null)
 * @method Analysis[]    findAll()
 * @method Analysis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnalysisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Analysis::class);
    }

    private function buildBasicQuery(Client $client, QueryBuilder $builder, string $select = 'a'): QueryBuilder
    {
        return $builder->select($select)
            ->from(Analysis::class, 'a')
            ->leftJoin('a.invoice', 'i')
            ->where($builder->expr()->eq('i.client', ':client'))
            ->setParameter(':client', $client->getId());
    }

    private function addFilters(array $filters, QueryBuilder $query, QueryBuilder $builder): QueryBuilder
    {
        if ($ids = $filters['ids']) {
            $query->andWhere($builder->expr()->in('a.id', ':ids'))
                ->setParameter(':ids', $ids);
        }

        if ($invoices = $filters['invoices']) {
            $query->andWhere($builder->expr()->in('i.id', ':invoiceIds'))
                ->setParameter(':invoiceIds', $invoices);
        }

        if ($status = $filters['status']) {
            $query->andWhere($builder->expr()->eq('a.status', ':status'))
                ->setParameter(':status', $status);
        }

        return $query;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function findByFilters(Client $client, array $filters, ?Pagination $pagination = null): Paginator
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $this->buildBasicQuery($client, $builder);
        $query = $this->addFilters($filters, $query, $builder);

        if ($pagination) {
            $sort = $pagination->getSort();
            if (!empty($sort)) {
                $sortOrder = $pagination->getSortOrder();
                switch ($sort) {
                    case 'id':
                        $query->orderBy('a.id', $sortOrder);
                        break;
                    case 'invoices':
                        $query->orderBy('i.id', $sortOrder);
                        break;
                    case 'status':
                        $query->orderBy('a.status', $sortOrder);
                        break;
                    case 'created_at':
                        $query->orderBy('a.createdAt', $sortOrder);
                        break;
                    default:
                        break;
                }
                $query->addOrderBy('a.id', 'asc');
            }

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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     */
    public function getByCriteria(Client $client, array $criteria): ?Analysis
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $this->buildBasicQuery($client, $builder);

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\Invoice\Analysis\Id:
                    $query->andWhere($builder->expr()->eq('a.id', ':id'))
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
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     */
    public function deleteByFilters(Client $client, array $filters): void
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $this->buildBasicQuery($client, $builder, 'a.id');
        $query = $this->addFilters($filters, $query, $builder);

        $analysesFound = $query->getQuery()->getResult();

        if (!empty($analysesFound)) {
            $analysisIds = array_map(fn(array $a) => $a['id'], $analysesFound);

            $builder = $this->getEntityManager()->createQueryBuilder();
            $deleteQuery = $builder->delete(Analysis::class, 'a')
                ->where($builder->expr()->in('a.id', ':ids'))
                ->setParameter(':ids', $analysisIds);
            $deleteQuery->getQuery()->execute();
        }
    }
}
