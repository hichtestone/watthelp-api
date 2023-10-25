<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Tax;
use App\Query\Criteria;
use App\Request\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tax|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tax|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tax[]    findAll()
 * @method Tax[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tax::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(Client $client, array $criteria): ?Tax
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('t')
            ->from(Tax::class, 't');

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\Tax\Id:
                    $query->andWhere($builder->expr()->eq('t.id', ':id'))
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
     * Search a collection by filters.
     *
     * @throws \InvalidArgumentException
     */
    public function findByFilters(Client $client, array $filters, ?Pagination $pagination = null): Paginator
    {
        $sort = null;
        if ($pagination) {
            $sort = $pagination->getSort();
        }

        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('t')
            ->from(Tax::class, 't')
            ->where($builder->expr()->eq('t.client', ':client'))
            ->setParameter(':client', $client->getId());

        // Filters
        if ($ids = $filters['id']) {
            $query->andWhere($builder->expr()->in('t.id', ':ids'))
                ->setParameter(':ids', $ids);
        }

        if (!empty($filters['exclude_ids'])) {
            $query->andWhere($builder->expr()->notIn('t.id', ':exclude_ids'))
                ->setParameter(':exclude_ids', $filters['exclude_ids']);
        }

        if (isset($filters['interval']['from']) && isset($filters['interval']['to'])) {
            $from = $filters['interval']['from'];
            $to = $filters['interval']['to'];

            $query->andWhere($builder->expr()->lte('t.startedAt', ':finishedAt'))
                ->setParameter(':finishedAt', $to, Type::DATETIME);

            $query->andWhere($builder->expr()->gte('t.finishedAt', ':startedAt'))
                ->setParameter(':startedAt', $from, Type::DATETIME);
        }

        // Sort
        if (!empty($sort)) {
            $sortOrder = $pagination->getSortOrder();
            switch ($sort) {
                case 'id':
                    $query->orderBy('t.id', $sortOrder);
                    break;
                case 'cspe':
                    $query->orderBy('t.cspe', $sortOrder);
                    break;
                case 'tdcfe':
                    $query->orderBy('t.tdcfe', $sortOrder);
                    break;
                case 'tccfe':
                    $query->orderBy('t.tccfe', $sortOrder);
                    break;
                case 'cta':
                    $query->orderBy('t.cta', $sortOrder);
                    break;
                case 'started_at':
                    $query->orderBy('t.startedAt', $sortOrder);
                    break;
                case 'finished_at':
                    $query->orderBy('t.finishedAt', $sortOrder);
                    break;

            }

            if ('id' !== $sort) {
                $query->addOrderBy('t.id', 'asc');
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
}