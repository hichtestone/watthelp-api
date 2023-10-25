<?php

declare(strict_types=1);

namespace App\Import;

use App\Entity\Import;
use App\Entity\ImportReport;
use App\Entity\Notification;
use App\Import\Importer\Invoice\InvoiceImporterManager;
use App\Import\Verifier\Invoice\VerifierManager;
use App\Manager\InvoiceManager;
use App\Message\ImportMessage;
use App\Service\LogService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class InvoiceImporter implements ImporterInterface
{
    private LogService $logService;
    private VerifierManager $verifierManager;
    private InvoiceImporterManager $invoiceImporterManager;
    private EntityManagerInterface $entityManager;
    private InvoiceManager $invoiceManager;
    private TranslatorInterface $translator;

    public function __construct(
        LogService $logService,
        VerifierManager $verifierManager,
        InvoiceImporterManager $invoiceImporterManager,
        EntityManagerInterface $entityManager,
        InvoiceManager $invoiceManager,
        TranslatorInterface $translator
    ) {
        $this->logService = $logService;
        $this->verifierManager = $verifierManager;
        $this->invoiceImporterManager = $invoiceImporterManager;
        $this->entityManager = $entityManager;
        $this->invoiceManager = $invoiceManager;
        $this->translator = $translator;
    }

    public function supports(string $type): bool
    {
        return $type === Import::TYPE_INVOICE;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     */
    public function import(string $localFilePath, Import $import, ImportReport $importReport, ImportMessage $importMessage, Notification $notification): ImportReport
    {
        $this->logService->sendNotification($import->getUser(), $notification, $this->translator->trans('file_verification_ongoing'));
        
        try {
            $this->entityManager->getConnection()->beginTransaction();

            $invoiceReferencesToReimport = $importMessage->getInvoiceReferences();
            if (!empty($invoiceReferencesToReimport)) {
                $this->invoiceManager->deleteByFilters($import->getUser()->getClient(), ['references' => $invoiceReferencesToReimport]);
            }

            $this->verifierManager->verify($localFilePath, $import, $invoiceReferencesToReimport, $import->getUser()->getClient());

            $this->logService->sendNotification($import->getUser(), $notification, $this->translator->trans('import_ongoing'));
            [$invoices, $anomalies] = $this->invoiceImporterManager->import($localFilePath, $import->getProvider(), $import->getUser()->getClient(), $invoiceReferencesToReimport);

            $importReport->setInvoices(new ArrayCollection($invoices));
            $importReport->setAnomalies(new ArrayCollection($anomalies));
            $importReport->setStatus($anomalies ? ImportReport::STATUS_WARNING : ImportReport::STATUS_OK);

            $this->entityManager->getConnection()->commit();

            return $importReport;

        } catch (\Throwable $t) {
            $this->entityManager->getConnection()->rollBack();
            throw $t;
        }
    }
}