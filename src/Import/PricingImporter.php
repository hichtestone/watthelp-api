<?php

declare(strict_types=1);

namespace App\Import;

use App\Entity\Import;
use App\Entity\ImportReport;
use App\Entity\Notification;
use App\Import\Importer\Pricing\PricingImporter as Importer;
use App\Import\Verifier\Pricing\PricingVerifier;
use App\Message\ImportMessage;
use App\Service\LogService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Contracts\Translation\TranslatorInterface;

class PricingImporter implements ImporterInterface
{
    private LogService $logService;
    private PricingVerifier $pricingVerifier;
    private Importer $importer;
    private TranslatorInterface $translator;

    public function __construct(
        LogService $logService,
        PricingVerifier $pricingVerifier,
        Importer $importer,
        TranslatorInterface $translator
    )
    {
        $this->logService = $logService;
        $this->pricingVerifier = $pricingVerifier;
        $this->importer = $importer;
        $this->translator = $translator;
    }

    public function supports(string $type): bool
    {
        return $type === Import::TYPE_PRICING;
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
        $importData = $this->pricingVerifier->verify($localFilePath, $import->getUser()->getClient());

        $this->logService->sendNotification($import->getUser(), $notification, $this->translator->trans('import_ongoing'));
        $pricingImported = $this->importer->import($importData, $import->getUser()->getClient());

        $importReport->setPricings(new ArrayCollection(array_values($pricingImported)));
        $importReport->setStatus(ImportReport::STATUS_OK);

        return $importReport;
    }
}
