<?php

declare(strict_types=1);

namespace App\Repository\Invoice;

use App\Entity\Invoice\InvoiceTax;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceTaxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceTax::class);
    }

    public function getTaxesAmountsOfDeliveryPointInvoices(array $deliveryPointInvoiceIds): array
    {
        if (empty($deliveryPointInvoiceIds)) {
            return [];
        }

        $builder = $this->getEntityManager()->createQueryBuilder();
        $query = $builder->select('it.total, it.type, dpi.id as dpiId')
            ->from(InvoiceTax::class, 'it')
            ->leftJoin('it.deliveryPointInvoices', 'dpi')
            ->where($builder->expr()->in('dpi.id', ':deliveryPointInvoices'))
            ->setParameter(':deliveryPointInvoices', $deliveryPointInvoiceIds);

        return $query->getQuery()->getResult();
    }
}