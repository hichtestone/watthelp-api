<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Role;
use App\Entity\User;
use App\Query\Criteria;
use App\Request\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(Client $client, array $criteria): ?Role
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('r')
            ->from(Role::class, 'r')
            ->andWhere($builder->expr()->eq('r.client', ':client'))
            ->setParameter(':client', $client->getId());

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\Role\Id:
                    $query->andWhere($builder->expr()->eq('r.id', ':id'))
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
     * @throws \InvalidArgumentException
     */
    public function findByFilters(Client $client, array $filters, ?Pagination $pagination = null): Paginator
    {
        $sort = null;
        if ($pagination) {
            $sort = $pagination->getSort();
        }
        
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('r')
            ->from(Role::class, 'r')
            ->andWhere($builder->expr()->eq('r.client', ':client'))
            ->setParameter(':client', $client->getId());

        if ($ids = $filters['ids']) {
            $query->andWhere($builder->expr()->in('r.id', ':ids'))
                ->setParameter(':ids', $ids);
        }

        if ($excludeIds = $filters['exclude_ids']) {
            $query->andWhere($builder->expr()->notIn('r.id', ':excludeIds'))
                ->setParameter(':excludeIds', $excludeIds);
        }

        if ($users = $filters['users']) {
            $query->innerJoin('r.users', 'u');
            $query->andWhere($builder->expr()->in('u.id', ':users'))
                ->setParameter(':users', array_map(fn (User $user) => $user->getId(), $users));
        }

        if ($permissions = $filters['permissions']) {
            $query->innerJoin('r.permissions', 'p');
            $query->andWhere($builder->expr()->in('p.code', ':permissions'))
                ->setParameter(':permissions', $permissions);
        }

        if ($name = $filters['name']) {
            $query->andWhere($builder->expr()->like('r.name', ':name'))
                ->setParameter(':name', "%$name%");
        }

        if ($description = $filters['description']) {
            $query->andWhere($builder->expr()->like('r.description', ':description'))
                ->setParameter(':description', "%$description%");
        }

        if (!empty($sort)) {
            $sortOrder = $pagination->getSortOrder();
            switch ($sort) {
                case 'id':
                    $query->orderBy('r.id', $sortOrder);
                    break;
                case 'name':
                    $query->orderBy('r.name', $sortOrder);
                    break;
                case 'description':
                    $query->orderBy('r.description', $sortOrder);
                    break;
                case 'created_at':
                    $query->orderBy('r.createdAt', $sortOrder);
                    break;
                case 'updated_at':
                    $query->orderBy('r.updatedAt', $sortOrder);
                    break;
            }

            if ('id' !== $sort) {
                $query->addOrderBy('r.id', 'asc');
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