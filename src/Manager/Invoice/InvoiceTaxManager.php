<?php

declare(strict_types=1);

namespace App\Manager\Invoice;

use App\Entity\Invoice\InvoiceTax;
use App\Repository\Invoice\InvoiceTaxRepository;
use Doctrine\ORM\EntityManagerInterface;

class InvoiceTaxManager
{
    private InvoiceTaxRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(InvoiceTax::class);
    }

    public function getTaxesAmountsOfDeliveryPointInvoices(array $deliveryPointInvoiceIds): array
    {
        return $this->repository->getTaxesAmountsOfDeliveryPointInvoices($deliveryPointInvoiceIds);
    }
}