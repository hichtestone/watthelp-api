<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Import;
use App\Entity\ImportReport;
use App\Entity\Notification;
use App\Exceptions\ImportException;
use App\Exceptions\ImportInvoiceAlreadyExistingException;
use App\Exceptions\InvalidFileException;
use App\Import\ImporterManager;
use App\Manager\ImportManager;
use App\Manager\ImportReportManager;
use App\Message\ImportMessage;
use App\Query\Criteria;
use App\Service\LogService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ImportMessageHandler implements MessageHandlerInterface
{
    private ImportManager $importManager;
    private ImportReportManager $importReportManager;
    private ImporterManager $importerManager;
    private LogService $logService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ImportManager $importManager,
        ImportReportManager $importReportManager,
        ImporterManager $importerManager,
        LogService $logService,
        EntityManagerInterface $entityManager
    ) {
        $this->importManager = $importManager;
        $this->importReportManager = $importReportManager;
        $this->importerManager = $importerManager;
        $this->logService = $logService;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws \Exception
     */
    public function __invoke(ImportMessage $message): void
    {
        $import = $this->importManager->getByCriteria([new Criteria\Import\Id($message->getImportId())]);
        if (!$import) {
            throw new \InvalidArgumentException('Impossible de récupérer la demande d\'import ' . $message->getImportId());
        }

        $importReport = new ImportReport();
        $importReport->setUser($import->getUser());
        $importReport->setImport($import);

        $localFile = sys_get_temp_dir() . '/tmp_file';

        try {
            if (!copy($import->getFile()->getRaw(), $localFile)) {
                throw new InvalidFileException('Impossible de copier le fichier.');
            }

            $notification = new Notification();

            $importReport = $this->importerManager->import($localFile, $import, $importReport, $message, $notification);

        } catch(\Throwable $t) {
            $importReport->setStatus(ImportReport::STATUS_ERROR);
            if ($t instanceof InvalidFileException) {
                $importReport->addMessage($t->getMessage());
                $this->logService->sendNotification($import->getUser(), null, $t->getMessage());
            } else if ($t instanceof ImportException) {
                if ($t instanceof ImportInvoiceAlreadyExistingException) {
                    $importReport->setInvoices(new ArrayCollection($t->getInvoices()));
                }
                $importReport->setMessages($t->getErrorMessages());
                $this->logService->sendNotification($import->getUser(), null, implode(' ', $t->getErrorMessages()));
            } else {
                $this->logService->critical($t->getMessage());
                $this->logService->critical("File: {$t->getFile()} - Line: {$t->getLine()}");
                $this->logService->critical($t->getTraceAsString());
                /**
                 * Doctrine closes the entity manager in case of database error like duplicate key
                 * which prevents us from saving the import report and notifying the user
                 * Reload the managers and reattach import/user by reloading the import
                 */
                if (!$this->entityManager->isOpen()) {
                    $this->resetManagers();
                    $import = $this->entityManager->find(Import::class, $import->getId());
                    $importReport->setUser($import->getUser());
                    $importReport->setImport($import);
                }
                $error = 'Une erreur inconnue est survenue lors de l\'import.';
                $importReport->addMessage($error);
                $this->logService->sendNotification($import->getUser(), null, $error);
            }
            throw $t;
        } finally {
            $this->importReportManager->insert($importReport);
            $this->logService->sendNotification($import->getUser(), $notification,
                'Le rapport d\'import est disponible.',
                [
                    'report_id' => $importReport->getId(),
                    'report_type' => $import->getType()
                ]
            );
            unlink($localFile);
        }
    }


    private function resetManagers(): void
    {
        $this->entityManager = $this->entityManager->create(
            $this->entityManager->getConnection(),
            $this->entityManager->getConfiguration()
        );
        $this->importManager = new ImportManager($this->entityManager);
        $this->importReportManager = new ImportReportManager($this->entityManager);
        $this->logService->resetNotificationManager($this->entityManager);
    }
}