<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\File;
use App\Query\Criteria;
use Doctrine\ORM\EntityRepository;

class FileRepository extends EntityRepository
{
    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByCriteria(array $criteria): ?File
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('f')
            ->from(File::class, 'f');

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\File\Id:
                    $query->andWhere($builder->expr()->eq('f.id', ':id'))
                        ->setParameter(':id', $where->getCriteria());
                    break;
                default:
                    break;
            }
        }

        return $query->getQuery()->getOneOrNullResult();
    }
}