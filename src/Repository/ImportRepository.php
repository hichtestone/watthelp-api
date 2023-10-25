<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Import;
use App\Query\Criteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Import|null find($id, $lockMode = null, $lockVersion = null)
 * @method Import|null findOneBy(array $criteria, array $orderBy = null)
 * @method Import[]    findAll()
 * @method Import[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Import::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(array $criteria): ?Import
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('i')
            ->from(Import::class, 'i');

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\Import\Id:
                    $query->andWhere($builder->expr()->eq('i.id', ':id'))
                        ->setParameter(':id', $where->getCriteria());
                    break;
                default:
                    throw new \LogicException('Criteria invalid.');
            }
        }

        return $query->getQuery()->getOneOrNullResult();
    }
}