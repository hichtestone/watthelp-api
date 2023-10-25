<?php

declare(strict_types=1);

namespace App\Import;

use App\Entity\Import;
use App\Entity\ImportReport;
use App\Entity\Notification;
use App\Import\Importer\DeliveryPoint\DeliveryPointImporter as Importer;
use App\Import\Verifier\DeliveryPoint\DeliveryPointVerifier;
use App\Message\ImportMessage;
use App\Service\LogService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeliveryPointImporter implements ImporterInterface
{
    private LogService $logService;
    private DeliveryPointVerifier $deliveryPointVerifier;
    private Importer $importer;
    private TranslatorInterface $translator;

    public function __construct(
        LogService $logService,
        DeliveryPointVerifier $deliveryPointVerifier,
        Importer $importer,
        TranslatorInterface $translator
    ) {
        $this->logService = $logService;
        $this->deliveryPointVerifier = $deliveryPointVerifier;
        $this->importer = $importer;
        $this->translator = $translator;
    }

    public function supports(string $type): bool
    {
        return $type === Import::TYPE_SCOPE;
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
        $this->logService->sendNotification($import->getUser(), $notification, $this->translator->trans('file_verification_ongoing'));
        $importData = $this->deliveryPointVerifier->verify($localFilePath);
        
        $this->logService->sendNotification($import->getUser(), $notification, $this->translator->trans('import_ongoing'));
        $deliveryPointsImported = $this->importer->import($importData, $import->getUser()->getClient());

        $importReport->setDeliveryPoints(new ArrayCollection(array_values($deliveryPointsImported)));
        $importReport->setStatus(ImportReport::STATUS_OK);

        return $importReport;
    }
}