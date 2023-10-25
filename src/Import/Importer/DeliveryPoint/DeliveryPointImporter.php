<?php

declare(strict_types=1);

namespace App\Import\Importer\DeliveryPoint;

use App\Entity\Client;
use App\Entity\DeliveryPoint;
use App\Import\Importer\AbstractImporter;
use App\Manager\DeliveryPointManager;
use Doctrine\ORM\EntityManagerInterface;

class DeliveryPointImporter
{
    private EntityManagerInterface $entityManager;
    private DeliveryPointManager $deliveryPointManager;

    public function __construct(
        DeliveryPointManager $deliveryPointManager,
        EntityManagerInterface $entityManager
    ) {
        $this->deliveryPointManager = $deliveryPointManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function import(array $deliveryPointsImportData, Client $client): array
    {
        $existingDeliveryPoints = $this->deliveryPointManager->findByFilters($client, ['references' => array_keys($deliveryPointsImportData)]);
        $deliveryPoints = [];
        foreach ($existingDeliveryPoints as $existingDeliveryPoint) {
            $deliveryPoints[$existingDeliveryPoint->getReference()] = $existingDeliveryPoint;
        }

        $this->entityManager->getConnection()->beginTransaction();
        try {
            foreach ($deliveryPointsImportData as $importData) {
                $deliveryPoint = $deliveryPoints[$importData->reference] ?? null;
                $deliveryPoint ??= new DeliveryPoint();
                $deliveryPoint->setClient($client);
                $deliveryPoint->setReference($importData->reference);
                $deliveryPoint->setCode($importData->code);
                $deliveryPoint->setName($importData->name);
                $deliveryPoint->setAddress($importData->address);
                $deliveryPoint->setLatitude($importData->latitude);
                $deliveryPoint->setLongitude($importData->longitude);
                $deliveryPoint->setMeterReference($importData->meterReference);
                $deliveryPoint->setPower(str_replace(',', '.', $importData->power));
                $deliveryPoint->setDescription($importData->description);
                $deliveryPoint->setIsInScope($importData->isInScope);
                $deliveryPoint->setScopeDate($importData->scopeDate);
                if (!array_key_exists($importData->reference, $deliveryPoints)) {
                    $deliveryPoint->setCreationMode(DeliveryPoint::CREATION_MODE_SCOPE_IMPORT);
                }

                $deliveryPoints[$importData->reference] = $deliveryPoint;

                $this->entityManager->persist($deliveryPoint);
            }

            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();

            return $deliveryPoints;

        } catch (\Throwable $t) {
            $this->entityManager->getConnection()->rollBack();
            throw $t;
        }
    }
}