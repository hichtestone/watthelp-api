<?php

declare(strict_types=1);

namespace App\Import;

use App\Entity\Import;
use App\Entity\ImportReport;
use App\Entity\Notification;
use App\Import\Importer\Budget\BudgetImporter as Importer;
use App\Import\Verifier\Budget\BudgetVerifier;
use App\Message\ImportMessage;
use App\Service\LogService;
use Doctrine\Common\Collections\ArrayCollection;

class BudgetImporter implements ImporterInterface
{
    private LogService $logService;
    private BudgetVerifier $verifier;
    private Importer $importer;

    public function __construct(
        LogService $logService,
        BudgetVerifier $verifier,
        Importer $importer
    ) {
        $this->logService = $logService;
        $this->verifier = $verifier;
        $this->importer = $importer;
    }

    public function supports(string $type): bool
    {
        return $type === Import::TYPE_BUDGET;
    }

    /**
     * @throws \App\Exceptions\ImportException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Throwable
     */
    public function import(string $localFilePath, Import $import, ImportReport $importReport, ImportMessage $message, Notification $notification): ImportReport
    {
        $this->logService->sendNotification($import->getUser(), $notification, 'Vérification du fichier en cours.');
        $importData = $this->verifier->verify($localFilePath, $import->getUser()->getClient());
        
        $this->logService->sendNotification($import->getUser(), $notification, 'Import en cours.');
        $budgetsImported = $this->importer->import($importData, $import->getUser()->getClient());

        $importReport->setBudgets(new ArrayCollection(array_values($budgetsImported)));
        $importReport->setStatus(ImportReport::STATUS_OK);

        return $importReport;
    }
}