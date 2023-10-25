<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ImportReport;
use App\Request\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ImportReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImportReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImportReport[]    findAll()
 * @method ImportReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImportReportRepository extends ServiceEntityRepository
{
    /**
     * @throws \LogicException
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImportReport::class);
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
        $query = $builder->select('i')
            ->from(ImportReport::class, 'i');

        // Filters
        if ($ids = $filters['id']) {
            $query->andWhere($builder->expr()->in('i.id', ':ids'))
                ->setParameter(':ids', $ids);
        }

        if ($user = $filters['user']) {
            $query->andWhere($builder->expr()->eq('i.user', ':user'))
                ->setParameter(':user', $user->getId());
        }

        if ($status = $filters['status']) {
            $query->andWhere($builder->expr()->eq('i.status', ':status'))
                ->setParameter(':status', $status);
        }

        // Sort
        if (!empty($sort)) {
            $sortOrder = $pagination->getSortOrder();
            switch ($sort) {
                case 'id':
                    $query->orderBy('i.id', $sortOrder);
                    break;
                case 'created_at':
                    $query->orderBy('i.createdAt', $sortOrder);
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
}