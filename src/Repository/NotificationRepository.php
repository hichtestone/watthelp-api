<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use App\Query\Criteria;
use App\Request\Pagination;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends EntityRepository
{
    /**
     * @param array $criteria
     * @return Notification|null
     * @throws NonUniqueResultException
     */
    public function getByCriteria(array $criteria): ?Notification
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('n')
            ->from(Notification::class, 'n');

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\User\Id:
                    $query->andWhere($builder->expr()->eq('n.user', ':user'))
                        ->setParameter(':user', $where->getCriteria());
                    break;
                case $where instanceof Criteria\Notification\Id:
                    $query->andWhere($builder->expr()->eq('n.id', ':id'))
                        ->setParameter(':id', $where->getCriteria());
                    break;
                default:
                    throw new \LogicException(\sprintf('Criteria %s is not defined in %s', \get_class($where), __CLASS__));
            }
        }

        return $query->getQuery()->getOneOrNullResult();
    }

    public function findByFilters(array $filters, ?Pagination $pagination = null): Paginator
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('n')
            ->from(Notification::class, 'n');

        /** @var User $user */
        if ($user = $filters['user']) {
            $query->andWhere($builder->expr()->eq('n.user', $user->getId()));
        }

        if ($identifiers = $filters['id']) {
            if (is_array($identifiers)) {
                $query->andWhere($builder->expr()->in('n.id', ':identifiers'));
                $query->setParameter(':identifiers', $identifiers);
            } else {
                $query->andWhere($builder->expr()->eq('n.id', ':id'));
                $query->setParameter(':id', $identifiers);
            }
        }

        if ($message = $filters['message']) {
            $query->andWhere($builder->expr()->eq('n.message', ':message'));
            $query->setParameter(':message', $message);
        }

        if (isset($filters['is_read'])) {
            $query->andWhere($builder->expr()->eq('n.isRead', ':isRead'));
            $query->setParameter(':isRead', $filters['is_read']);
        }

        if ($pagination) {
            $sort = $pagination->getSort();
            if (!empty($sort)) {
                $sortOrder = $pagination->getSortOrder();
                switch ($sort) {
                    case 'id':
                        $query->orderBy('n.id', $sortOrder);
                        break;
                    case 'message':
                        $query->orderBy('n.message', $sortOrder);
                        break;
                    case 'url':
                        $query->orderBy('n.url', $sortOrder);
                        break;
                    case 'is_read':
                        $query->orderBy('n.isRead', $sortOrder);
                        break;
                    case 'created_at':
                        $query->orderBy('n.createdAt', $sortOrder);
                        break;
                    default:
                        break;
                }
                $query->addOrderBy('n.id', 'DESC');
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

    public function markAllAsRead(User $user): int
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->update(Notification::class, 'n')
            ->set('n.updatedAt', ':updatedAt')
            ->setParameter(':updatedAt', date('Y-m-d H:i:s'))
            ->set('n.isRead', ':isRead')
            ->setParameter(':isRead', true);
        $query->where($query->expr()->eq('n.user', ':user'))
            ->setParameter(':user', $user->getId());
        return $query->getQuery()->getResult();
    }
    
    /**
     * @param array $filters
     * @return mixed
     */
    public function deleteByFilters(array $filters): int
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->delete(Notification::class, 'n');
        /** @var User $user */
        if ($user = $filters['user']) {
            $query->andWhere($builder->expr()->eq('n.user', ':user'))
                ->setParameter(':user', $user->getId());
        }
        if ($ids = $filters['ids']) {
            if ($ids !== '*') {
                $query->andWhere($builder->expr()->in('n.id', ':ids'))
                    ->setParameter(':ids', $ids);
            }
        }
        return $query->getQuery()->getResult();
    }
}
