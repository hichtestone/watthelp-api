<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Permission;
use App\Entity\User;
use App\Query\Criteria;
use App\Request\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Permission|null find($id, $lockMode = null, $lockVersion = null)
 * @method Permission|null findOneBy(array $criteria, array $orderBy = null)
 * @method Permission[]    findAll()
 * @method Permission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permission::class);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(array $criteria): ?Permission
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('p')
            ->from(Permission::class, 'p');

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\Permission\Code:
                    $query->andWhere($builder->expr()->eq('p.code', ':code'))
                        ->setParameter(':code', $where->getCriteria());
                    break;
                case $where instanceof Criteria\Permission\Id:
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

    public function getByRoles(array $roles, string $select = 'p'): array
    {
        if (empty($roles)) {
            return [];
        }

        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select($select)
            ->from(Permission::class, 'p')
            ->innerJoin('p.roles', 'r')
            ->andWhere($builder->expr()->in('r.id', ':roles'))
            ->setParameter(':roles', $roles);

        return $query->getQuery()->execute();
    }

    public function getCodesByRoles(array $roles): array
    {
        return array_map(fn (array $row) => $row['code'], $this->getByRoles($roles, 'DISTINCT p.code'));
    }

    public function hasPermissions(User $user, array $permissions): bool
    {
        if (empty($permissions)) {
            return false;
        }

        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('COUNT(DISTINCT p.code) as countPermissionsFound')
            ->from(Permission::class, 'p')
            ->innerJoin('p.roles', 'r')
            ->innerJoin('r.users', 'u')
            ->where($builder->expr()->eq('u.id', ':userId'))
            ->setParameter(':userId', $user->getId())
            ->andWhere($builder->expr()->in('p.code', ':codes'))
            ->setParameter(':codes', $permissions);

        $queryResult = $query->getQuery()->execute();
        $numberOfPermissionsFound = intval($queryResult[0]['countPermissionsFound']);

        return $numberOfPermissionsFound === count($permissions);
    }

    public function findByFilters(array $filters, ?Pagination $pagination = null): Paginator
    {
        $sort = null;
        if ($pagination) {
            $sort = $pagination->getSort();
        }
        
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('p')
            ->from(Permission::class, 'p');

        if ($ids = $filters['ids']) {
            $query->andWhere($builder->expr()->in('p.id', ':ids'))
                ->setParameter(':ids', $ids);
        }

        if ($codes = $filters['codes']) {
            $query->andWhere($builder->expr()->in('p.code', ':codes'))
                ->setParameter(':codes', $codes);
        }

        if (!empty($sort)) {
            $sortOrder = $pagination->getSortOrder();
            $query->orderBy('p.id', $sortOrder);
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