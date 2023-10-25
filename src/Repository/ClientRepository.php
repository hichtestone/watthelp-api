<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Client;
use App\Query\Criteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(array $criteria): ?Client
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('u')
            ->from(Client::class, 'u');

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\Client\Id:
                    $query->andWhere($builder->expr()->eq('u.id', ':id'))
                        ->setParameter(':id', $where->getCriteria());
                    break;
                default:
                    throw new \LogicException('Criteria invalid.');
                    break;
            }
        }

        return $query->getQuery()->getOneOrNullResult();
    }
}
