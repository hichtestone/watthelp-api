<?php

declare(strict_types=1);

namespace App\Import\Importer\Pricing;

use App\Entity\Client;
use App\Entity\Pricing;
use App\Import\Importer\AbstractImporter;
use App\Manager\PricingManager;
use Doctrine\ORM\EntityManagerInterface;

class PricingImporter
{
    private EntityManagerInterface $entityManager;
    private PricingManager $pricingManager;

    public function __construct(
        PricingManager $pricingManager,
        EntityManagerInterface $entityManager
    )
    {
        $this->pricingManager = $pricingManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function import(array $pricingImportData, Client $client): array
    {
        $existingPrices = $this->pricingManager->findByFilters($client, ['name' => $pricingImportData[0]->name]);
        $prices = [];
        foreach ($existingPrices as $existingPrice) {
            $prices[$existingPrice->getName()] = $existingPrice;
        }

        $this->entityManager->getConnection()->beginTransaction();
        try {
            foreach ($pricingImportData as $dataUploaded) {
                $pricing = $prices[$dataUploaded->name] ?? null;
                $pricing ??= new Pricing();
                $pricing->setClient($client);
                $pricing->setName($dataUploaded->name);

                switch ($dataUploaded->type) {
                    case 'offre de marché':
                        $pricing->setType(Pricing::TYPE_NEGOTIATED);
                        break;
                    case 'trv':
                        $pricing->setType(Pricing::TYPE_REGULATED);
                        break;
                }

                $pricing->setConsumptionBasePrice($dataUploaded->consumptionBasePrice);
                $pricing->setSubscriptionPrice($dataUploaded->subscriptionPrice);
                $pricing->setStartedAt($dataUploaded->startedAt);
                $pricing->setFinishedAt($dataUploaded->finishedAt);

                $prices[$dataUploaded->name] = $pricing;

                $this->entityManager->persist($pricing);
            }

            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();

            return $prices;

        } catch (\Throwable $t) {
            $this->entityManager->getConnection()->rollBack();
            throw $t;
        }
    }
}
