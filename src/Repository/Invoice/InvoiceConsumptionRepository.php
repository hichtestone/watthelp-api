<?php

declare(strict_types=1);

namespace App\Repository\Invoice;

use App\Entity\Client;
use App\Entity\DeliveryPoint;
use App\Entity\Invoice\InvoiceConsumption;
use App\Model\Period;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

class InvoiceConsumptionRepository extends ServiceEntityRepository
{
    /**
     * @throws \LogicException
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceConsumption::class);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getConsumptionsBetweenInterval(Client $client, Period $period, array $deliveryPoints = []): array
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('ic')
            ->from(InvoiceConsumption::class, 'ic')
            ->leftJoin('ic.deliveryPointInvoice', 'dpi')
            ->leftJoin('dpi.invoice', 'i');

        $query->where($builder->expr()->eq('i.client', ':client'))
            ->setParameter(':client', $client->getId());

        if ($deliveryPoints) {
            $query->andWhere($builder->expr()->in('dpi.deliveryPoint', ':deliveryPoints'))
                ->setParameter(':deliveryPoints', array_map(fn (DeliveryPoint $dp)  => $dp->getId(), $deliveryPoints));
        }

        $query->andWhere($builder->expr()->orX(
            $builder->expr()->andX(
                $builder->expr()->gte('ic.finishedAt', ':from'),
                $builder->expr()->lte('ic.finishedAt', ':to')
            ),
            $builder->expr()->andX(
                $builder->expr()->gte('ic.startedAt', ':from'),
                $builder->expr()->lte('ic.startedAt', ':to')
            )
        ))
        ->setParameter(':from', $period->getStart(), Types::DATETIME_MUTABLE)
        ->setParameter(':to', $period->getEnd(), Types::DATETIME_MUTABLE);

        return $query->getQuery()->getResult();
    }
}