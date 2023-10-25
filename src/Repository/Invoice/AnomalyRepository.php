<?php

declare(strict_types=1);

namespace App\Repository\Invoice;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\Invoice\Anomaly;
use App\Query\Criteria;
use App\Request\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Anomaly|null find($id, $lockMode = null, $lockVersion = null)
 * @method Anomaly|null findOneBy(array $criteria, array $orderBy = null)
 * @method Anomaly[]    findAll()
 * @method Anomaly[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnomalyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Anomaly::class);
    }

    private function buildBasicQuery(Client $client, QueryBuilder $builder, string $select = 'a'): QueryBuilder
    {
        $query = $builder->select($select)
            ->from(Anomaly::class, 'a')
            ->leftJoin('a.itemAnalysis', 'ia')
            ->leftJoin('ia.analysis', 'analys')
            ->leftJoin('analys.invoice', 'i')
            ->where($builder->expr()->eq('i.client', ':client'))
            ->setParameter(':client', $client->getId());

        return $query;
    }

    private function addFilters(array $filters, QueryBuilder $query, QueryBuilder $builder): QueryBuilder
    {
        if ($ids = $filters['id']) {
            $query->andWhere($builder->expr()->in('a.id', ':ids'))
                ->setParameter(':ids', $ids);
        }

        if ($invoices = $filters['invoices']) {
            $query->andWhere($builder->expr()->in('i.id', ':invoiceIds'))
                ->setParameter(':invoiceIds', $invoices);
        }

        if ($invoiceRef = $filters['invoice_reference']) {
            $query->andWhere($builder->expr()->like('i.reference', ':invoice_reference'));
            $query->setParameter(':invoice_reference', '%'.$invoiceRef.'%');
        }

        if ($status = $filters['status']) {
            $query->andWhere($builder->expr()->eq('a.status', ':status'));
            $query->setParameter(':status', $status);
        }

        if ($total = $filters['total']) {
            $query->andWhere($builder->expr()->gte('a.total', ':total'));
            $query->setParameter(':total', $total);
        }

        if ($totalPercentage = $filters['total_percentage']) {
            $query->andWhere($builder->expr()->gte('a.totalPercentage', ':totalPercentage'));
            $query->setParameter(':totalPercentage', $totalPercentage);
        }

        if ($profit = $filters['profit']) {            
            $query->andWhere($builder->expr()->eq('a.profit', ':profit'))
                ->setParameter(':profit', $profit);
        }

        if (!empty($filters['created'])) {
            [
                'from' => $from,
                'to' => $to
            ] = $filters['created'];
            $query->andWhere($builder->expr()->gte('a.createdAt', ':createdFrom'))
                ->setParameter(':createdFrom', $from);
            $query->andWhere($builder->expr()->lt('a.createdAt', ':createdTo'))
                ->setParameter(':createdTo', (new \DateTime($to))->add(new \DateInterval('P1D'))->format('Y-m-d'));
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
                    case 'content':
                        $query->orderBy('a.content', $sortOrder);
                        break;
                    case 'status':
                        $query->orderBy('a.status', $sortOrder);
                        break;
                    case 'total':
                        $query->orderBy('a.total', $sortOrder);
                        break;
                    case 'total_percentage':
                        $query->orderBy('a.totalPercentage', $sortOrder);
                        break;
                    case 'created_at':
                        $query->orderBy('a.createdAt', $sortOrder);
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
     * @throws InvalidArgumentException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByCriteria(Client $client, array $criteria): ?Anomaly
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $this->buildBasicQuery($client, $builder);

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\Invoice\Anomaly\Id:
                    $query->andWhere($builder->expr()->eq('a.id', ':id'))
                        ->setParameter(':id', $where->getCriteria());
                    break;
            }
        }

        return $query->getQuery()->getOneOrNullResult();
    }

    public function deleteByFilters(Client $client, array $filters): void
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $this->buildBasicQuery($client, $builder, 'a.id');
        $query = $this->addFilters($filters, $query, $builder);

        $anomaliesFound = $query->getQuery()->getResult();

        if (!empty($anomaliesFound)) {
            $this->getEntityManager()->createQueryBuilder()->delete(Anomaly::class, 'a')
                ->andWhere($builder->expr()->in('a.id', ':ids'))
                ->setParameter(':ids', array_map(fn(array $a) => $a['id'], $anomaliesFound))
                ->getQuery()
                ->execute();
        }
    }

    public function getCountAnomalies(Client $client): array
    {
        $sql =
            'SELECT ' .
            'COUNT(a.id) as num_anomalies ';

        $sql .= ',SUM(CASE WHEN a.status = "' . Anomaly::STATUS_IGNORED . '" THEN 1 ELSE 0 END) as stat_' . Anomaly::STATUS_IGNORED;
        $sql .= ',SUM(CASE WHEN a.status = "' . Anomaly::STATUS_UNSOLVED . '" THEN 1 ELSE 0 END) as stat_' . Anomaly::STATUS_UNSOLVED;
        $sql .= ',SUM(CASE WHEN a.status = "' . Anomaly::STATUS_PROCESSING . '" THEN 1 ELSE 0 END) as stat_' . Anomaly::STATUS_PROCESSING;
        $sql .= ',SUM(CASE WHEN a.status = "' . Anomaly::STATUS_SOLVED . '" THEN 1 ELSE 0 END) as stat_' . Anomaly::STATUS_SOLVED;

        $sql .= ' FROM anomaly a';
        $sql .= ' LEFT JOIN item_analysis iia ON iia.id = a.item_analysis_id';
        $sql .= ' LEFT JOIN analysis ia ON ia.id = iia.analysis_id';
        $sql .= ' LEFT JOIN invoice i ON i.id = ia.invoice_id';

        $sql .= ' WHERE 1 = 1';

        if (isset($client)) {
            $sql .= ' AND i.client_id = ' . $client->getId();
        }

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetch();
    }
}