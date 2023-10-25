<?php

declare(strict_types=1);

namespace App\Repository\Invoice;

use App\Entity\Client;
use App\Entity\DeliveryPoint;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Query\Criteria;
use App\Request\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class DeliveryPointInvoiceRepository extends ServiceEntityRepository
{
    /**
     * @throws \LogicException
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryPointInvoice::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(Client $client, array $criteria): ?DeliveryPointInvoice
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('dpi')
            ->from(DeliveryPointInvoice::class, 'dpi')
            ->innerJoin('dpi.invoice', 'i')
            ->where($builder->expr()->eq('i.client', ':client'))
            ->setParameter(':client', $client->getId());

        foreach ($criteria as $where) {
            switch (true) {
                case $where instanceof Criteria\Invoice\DeliveryPointInvoice\Id:
                    $query->andWhere($builder->expr()->eq('dpi.id', ':id'))
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
        $this->getEntityManager()->getConfiguration()->addCustomDatetimeFunction('YEAR', 'DoctrineExtensions\Query\Mysql\Year');

        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('dpi')
            ->from(DeliveryPointInvoice::class, 'dpi')
            ->innerJoin('dpi.invoice', 'i')
            ->innerJoin('dpi.deliveryPoint', 'd')
            ->where($builder->expr()->eq('i.client', ':client'))
            ->setParameter(':client', $client->getId());

        if ($ids = $filters['ids']) {
            $query->andWhere($builder->expr()->in('dpi.id', ':ids'))
                ->setParameter(':ids', $ids);
        }

        if ($deliveryPoint = $filters['delivery_point']) {
            $query->andWhere($builder->expr()->eq('dpi.deliveryPoint', $deliveryPoint->getId()));
        }

        if ($invoiceReference = $filters['invoice_reference']) {
            $query->andWhere($builder->expr()->like('i.reference', ':invoiceReference'))
                ->setParameter(':invoiceReference', "%$invoiceReference%");
        }

        if ($deliveryPointReference = $filters['delivery_point_reference']) {
            $query->andWhere($builder->expr()->like('d.reference', ':deliveryPointReference'))
                ->setParameter(':deliveryPointReference', "%$deliveryPointReference%");
        }

        if ($deliveryPointName = $filters['delivery_point_name']) {
            $query->andWhere($builder->expr()->like('d.name', ':deliveryPointName'))
                ->setParameter(':deliveryPointName', "%$deliveryPointName%");
        }

        if ($emittedAt = $filters['emitted_at']) {
            if (is_array($emittedAt)) {
                if (!empty($emittedAt['min'])) {
                    $query->andWhere($builder->expr()->gt('i.emittedAt', ':minEmittedAt'))
                        ->setParameter(':minEmittedAt', $emittedAt['min'], Types::DATETIME_MUTABLE);
                }
                if (!empty($emittedAt['max'])) {
                    $query->andWhere($builder->expr()->lt('i.emittedAt', ':maxEmittedAt'))
                        ->setParameter(':maxEmittedAt', $emittedAt['max'], Types::DATETIME_MUTABLE);
                }
            } else {
                $query->andWhere($builder->expr()->eq('i.emittedAt', ':emittedAt'))
                    ->setParameter(':emittedAt', $emittedAt, Types::DATETIME_MUTABLE);
            }
        }

        if (isset($filters['year'])) {
            $query->andWhere($builder->expr()->eq('YEAR(i.emittedAt)', ':year'))
                ->setParameter(':year', $filters['year']);
        }

        if (isset($filters['is_credit_note'])) {
            $query->innerJoin('dpi.consumption', 'ic');
            if ($filters['is_credit_note']) {
                $query->andWhere($builder->expr()->lt('ic.quantity', 0));
            } else {
                $query->andWhere($builder->expr()->gte('ic.quantity', 0));
            }
        }

        if ($pagination) {
            $sort = $pagination->getSort();
            if (!empty($sort)) {
                $sortOrder = $pagination->getSortOrder();
                switch ($sort) {
                    case 'id':
                        $query->orderBy('dpi.id', $sortOrder);
                        break;
                    case 'amount_ht':
                        $query->orderBy('dpi.amountHT', $sortOrder);
                        break;
                    case 'amount_tva':
                        $query->orderBy('dpi.amountTVA', $sortOrder);
                        break;
                    case 'amount_ttc':
                        $query->orderBy('dpi.amountTTC', $sortOrder);
                        break;
                    case 'emitted_at':
                        $query->orderBy('i.emittedAt', $sortOrder);
                        break;
                    default:
                        break;
                }
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

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     */
    public function getPrevious(DeliveryPointInvoice $deliveryPointInvoice, \DateTimeInterface $emittedAt = null): ?DeliveryPointInvoice
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('dpi')
            ->from(DeliveryPointInvoice::class, 'dpi')
            ->innerJoin('dpi.invoice', 'i')
            ->innerJoin('dpi.deliveryPoint', 'pdl');

        $class = get_class($deliveryPointInvoice->getInvoice());
        $query->andWhere(sprintf('i INSTANCE OF %s', $class));

        if (isset($emittedAt)) {
            $query->where($builder->expr()->lte('i.emittedAt', ':emittedAt'))
                ->setParameter(':emittedAt', $emittedAt, Types::DATETIME_MUTABLE);
        } else {
            $query->where($builder->expr()->lt('i.emittedAt', ':emittedAt'))
                ->setParameter(':emittedAt', $deliveryPointInvoice->getInvoice()->getEmittedAt(), Types::DATETIME_MUTABLE);
        }

        $query->andWhere($builder->expr()->eq('pdl.reference', ':reference'))
            ->setParameter(':reference', $deliveryPointInvoice->getDeliveryPoint()->getReference());

        $query->andWhere($builder->expr()->gt('i.amountHT', 0));

        $query->orderBy('i.emittedAt', 'DESC');
        $query->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     */
    public function hasBefore(DeliveryPoint $deliveryPoint, \DateTimeInterface $consumptionEnd): int
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('COUNT(dpi)')
            ->from(DeliveryPointInvoice::class, 'dpi')
            ->leftJoin('dpi.consumption', 'c');

        $query->andWhere($builder->expr()->eq('dpi.deliveryPoint', ':deliveryPoint'))
            ->setParameter(':deliveryPoint', $deliveryPoint->getId());

        $query->andWhere($builder->expr()->lte('c.indexFinishedAt', ':consumptionEnd'))
            ->setParameter(':consumptionEnd', $consumptionEnd);

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     */
    public function hasRealInvoiceBetweenInterval(DeliveryPoint $deliveryPoint, \DateTimeInterface $from, \DateTimeInterface $to): int
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('COUNT(dpi)')
            ->from(DeliveryPointInvoice::class, 'dpi')
            ->leftJoin('dpi.consumption', 'c');

        $query->andWhere($builder->expr()->eq('dpi.deliveryPoint', ':deliveryPoint'))
            ->setParameter(':deliveryPoint', $deliveryPoint->getId());

        $query->andWhere($builder->expr()->eq('dpi.type', ':type'))
            ->setParameter(':type', DeliveryPointInvoice::TYPE_REAL);

        $query->andWhere($builder->expr()->gte('c.indexFinishedAt', ':from'))
            ->setParameter(':from', $from);

        $query->andWhere($builder->expr()->lte('c.indexFinishedAt', ':to'))
            ->setParameter(':to', $to);

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     */
    public function getSumConsumptionBetweenInterval(DeliveryPoint $deliveryPoint, \DateTimeInterface $from, \DateTimeInterface $to): int
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('SUM(c.quantity)')
            ->from(DeliveryPointInvoice::class, 'dpi')
            ->leftJoin('dpi.consumption', 'c');

        $query->andWhere($builder->expr()->eq('dpi.deliveryPoint', ':deliveryPoint'))
            ->setParameter(':deliveryPoint', $deliveryPoint->getId());

        $query->andWhere($builder->expr()->gte('c.indexFinishedAt', ':from'))
            ->setParameter(':from', $from);

        $query->andWhere($builder->expr()->lte('c.indexFinishedAt', ':to'))
            ->setParameter(':to', $to);

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    public function getAmountsBetweenInterval(Client $client, ?\DateTimeInterface $start = null, ?\DateTimeInterface $end = null): array
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('dpi.id AS dpiId, ic.total AS consumptionTotal, ic.startedAt AS consumptionStartedAt, ic.finishedAt AS consumptionFinishedAt, isu.total as subscriptionTotal')
            ->from(DeliveryPointInvoice::class, 'dpi')
            ->innerJoin('dpi.invoice', 'i')
            ->innerJoin('dpi.consumption', 'ic')
            ->leftJoin('dpi.subscription', 'isu')
            ->where($builder->expr()->eq('i.client', ':client'))
            ->setParameter(':client', $client->getId());

        if ($start && $end) {
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
            ->setParameter(':from', $start, Types::DATETIME_MUTABLE)
            ->setParameter(':to', $end, Types::DATETIME_MUTABLE);
        } else if ($start) {
            $query->andWhere($builder->expr()->gt('ic.finishedAt', ':from'))
                ->setParameter(':from', $start, Types::DATETIME_MUTABLE);
        } else if ($end) {
            $query->andWhere($builder->expr()->lt('ic.startedAt', ':to'))
                ->setParameter(':to', $end, Types::DATETIME_MUTABLE);
        }

        return $query->getQuery()->getResult();
    }
}
