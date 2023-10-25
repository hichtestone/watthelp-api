<?php

declare(strict_types=1);

namespace App\Manager\Invoice;

use App\Entity\Client;
use App\Entity\Invoice\InvoiceConsumption;
use App\Factory\PeriodFactory;
use App\Model\Period;
use App\Repository\Invoice\InvoiceConsumptionRepository;
use Doctrine\ORM\EntityManagerInterface;

class InvoiceConsumptionManager
{
    private InvoiceConsumptionRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(InvoiceConsumption::class);
    }

    public function getConsumptionsBetweenInterval(Client $client, Period $period, array $deliveryPoints = []): array
    {
        return $this->repository->getConsumptionsBetweenInterval($client, $period, $deliveryPoints);
    }
 
    public function getConsumptionsOfYear(Client $client, int $year, array $deliveryPoints = []): array
    {
        return $this->getConsumptionsBetweenInterval($client, PeriodFactory::createFromYear($year), $deliveryPoints);
    }
}