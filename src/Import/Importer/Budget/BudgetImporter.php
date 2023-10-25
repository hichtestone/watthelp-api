<?php

declare(strict_types=1);

namespace App\Import\Importer\Budget;

use App\Entity\Budget;
use App\Entity\Budget\DeliveryPointBudget;
use App\Entity\Client;
use App\Manager\BudgetManager;
use App\Manager\DeliveryPointManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class BudgetImporter
{
    private EntityManagerInterface $entityManager;
    private BudgetManager $budgetManager;
    private DeliveryPointManager $deliveryPointManager;

    public function __construct(
        BudgetManager $budgetManager,
        DeliveryPointManager $deliveryPointManager,
        EntityManagerInterface $entityManager
    ) {
        $this->budgetManager = $budgetManager;
        $this->deliveryPointManager = $deliveryPointManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function import(array $budgetsImportData, Client $client): array
    {
        [$budgets, $deliveryPoints] = $this->getEntitiesIndexed($budgetsImportData, $client);

        $this->entityManager->getConnection()->beginTransaction();
        try {
            foreach ($budgetsImportData as $importData) {
                $budget = $budgets[$importData->year] ?? null;
                $budget ??= new Budget();
                $budget->setClient($client);
                $budget->setYear($importData->year);
                $budget->setTotalHours($importData->totalHours);
                $budget->setAveragePrice($importData->averagePrice);
                $budget->setTotalConsumption($importData->totalConsumption);
                $budget->setTotalAmount($importData->totalAmount);

                $existingDpBudgets = [];
                foreach ($budget->getDeliveryPointBudgets() as $existingDpBudget) {
                    $existingDpBudgets[$existingDpBudget->getDeliveryPoint()->getReference()] = $existingDpBudget;
                }

                $dpBudgets = [];
                foreach ($importData->dpBudgets as $dpBudgetData) {
                    $dpBudget = $existingDpBudgets[$dpBudgetData->dpRef] ?? new DeliveryPointBudget();
                    $dpBudget->setDeliveryPoint($deliveryPoints[$dpBudgetData->dpRef]);
                    $dpBudget->setBudget($budget);
                    $dpBudget->setInstalledPower($dpBudgetData->installedPower);
                    $dpBudget->setEquipmentPowerPercentage($dpBudgetData->equipmentPowerPercentage);
                    $dpBudget->setGradation($dpBudgetData->gradation);
                    $dpBudget->setGradationHours($dpBudgetData->gradationHours);
                    $dpBudget->setSubTotalConsumption($dpBudgetData->subTotalConsumption);
                    $dpBudget->setRenovation($dpBudgetData->renovation);
                    $dpBudget->setRenovatedAt($dpBudgetData->renovatedAt);
                    $dpBudget->setNewInstalledPower($dpBudgetData->newInstalledPower);
                    $dpBudget->setNewEquipmentPowerPercentage($dpBudgetData->newEquipmentPowerPercentage);
                    $dpBudget->setNewGradation($dpBudgetData->newGradation);
                    $dpBudget->setNewGradationHours($dpBudgetData->newGradationHours);
                    $dpBudget->setNewSubTotalConsumption($dpBudgetData->newSubTotalConsumption);
                    $dpBudget->setTotalConsumption($dpBudgetData->totalConsumption);
                    $dpBudget->setTotal($dpBudgetData->total);
                    $dpBudgets[] = $dpBudget;
                }
                $budget->setDeliveryPointBudgets(new ArrayCollection($dpBudgets));

                $budgets[$importData->year] = $budget;

                $this->entityManager->persist($budget);
            }

            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();

            return $budgets;

        } catch (\Throwable $t) {
            $this->entityManager->getConnection()->rollBack();
            throw $t;
        }
    }

    private function getEntitiesIndexed(array $budgetsImportData, Client $client): array
    {
        $budgetYears = $dpRefs = $budgets = $deliveryPoints = [];
        foreach ($budgetsImportData as $budgetImportData) {
            $budgetYears[] = $budgetImportData->year;
            foreach ($budgetImportData->dpBudgets as $dpBudgetData) {
                $dpRefs[$dpBudgetData->dpRef] ??= $dpBudgetData->dpRef;
            }
        }

        $existingBudgets = $this->budgetManager->findByFilters($client, ['years' => array_keys($budgetsImportData)]);
        foreach ($existingBudgets as $existingBudget) {
            $budgets[$existingBudget->getYear()] = $existingBudget;
        }

        $existingDeliveryPoints = $this->deliveryPointManager->findByFilters($client, ['references' => array_values($dpRefs)]);
        foreach ($existingDeliveryPoints as $existingDeliveryPoint) {
            $deliveryPoints[$existingDeliveryPoint->getReference()] = $existingDeliveryPoint;
        }

        return [$budgets, $deliveryPoints];
    }
}