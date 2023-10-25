<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\User;
use App\Query\Criteria;
use App\Request\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @throws NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(?Client $client, array $criteria): ?User
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('u')
            ->from(User::class, 'u');

        if ($client) {
            $query->andWhere($builder->expr()->eq('u.client', ':client'))
                ->setParameter(':client', $client->getId());
        }

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\User\Id:
                    $query->andWhere($builder->expr()->eq('u.id', ':id'))
                        ->setParameter(':id', $where->getCriteria());
                    break;
                case $where instanceof Criteria\User\Email:
                    $query->andWhere($builder->expr()->eq('u.email', ':email'))
                        ->setParameter(':email', $where->getCriteria());
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
        $query = $builder->select('u')
            ->from(User::class, 'u')
            ->where($builder->expr()->eq('u.client', ':client'))
            ->setParameter(':client', $client->getId());

        if ($ids = $filters['id']) {
            $query->andWhere($builder->expr()->in('u.id', ':ids'))
                ->setParameter(':ids', $ids);
        }

        if (!empty($filters['exclude_ids'])) {
            $query->andWhere($builder->expr()->notIn('u.id', ':exclude_ids'))
                ->setParameter(':exclude_ids', $filters['exclude_ids']);
        }

        if ($firstName = $filters['first_name']) {
            $query->andWhere($builder->expr()->like('u.firstName', ':firstName'))
                ->setParameter(':firstName', '%'.$firstName.'%');
        }

        if ($lastName = $filters['last_name']) {
            $query->andWhere($builder->expr()->like('u.lastName', ':lastName'))
                ->setParameter(':lastName', '%'.$lastName.'%');
        }

        if ($email = $filters['email']) {
            $query->andWhere($builder->expr()->like('u.email', ':email'))
                ->setParameter(':email', '%'.$email.'%');
        }

        if (isset($filters['phone'])) {
            $query->andWhere($builder->expr()->like('u.phone', ':phone'));
            $query->setParameter(':phone', '%'.$filters['phone'].'%');
        }

        if (isset($filters['mobile'])) {
            $query->andWhere($builder->expr()->like('u.mobile', ':mobile'));
            $query->setParameter(':mobile', '%'.$filters['mobile'].'%');
        }

        // Sort
        if (!empty($sort)) {
            $sortOrder = $pagination->getSortOrder();
            switch ($sort) {
                case 'id':
                    $query->orderBy('u.id', $sortOrder);
                    break;
                case 'first_name':
                    $query->orderBy('u.firstName', $sortOrder);
                    break;
                case 'last_name':
                    $query->orderBy('u.lastName', $sortOrder);
                    break;
                case 'email':
                    $query->orderBy('u.email', $sortOrder);
                    break;
                case 'phone':
                    $query->orderBy('u.phone', $sortOrder);
                    break;
                case 'mobile':
                    $query->orderBy('u.mobile', $sortOrder);
                    break;
            }

            if ('id' !== $sort) {
                $query->addOrderBy('u.id', 'asc');
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