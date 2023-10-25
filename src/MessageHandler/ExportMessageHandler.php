<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\User;
use App\Export\ExporterManager;
use App\Message\ExportMessage;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ExportMessageHandler implements MessageHandlerInterface
{
    private LogService $logService;
    private ExporterManager $exporterManager;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ExporterManager $exporterManager,
        LogService $logService,
        EntityManagerInterface $entityManager
    ) {
        $this->exporterManager = $exporterManager;
        $this->logService = $logService;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws \Exception
     */
    public function __invoke(ExportMessage $message): void
    {
        $user = $this->entityManager->getReference(User::class, $message->getUserId());
        $notification = $this->logService->sendNotification($user, null, 'L\'export est en cours.');

        try {

            $filters = $message->getFilters();

            $url = $this->exporterManager->export($message->getType(), $message->getFormat(), $filters, $user);

            $this->logService->sendNotification($user, $notification, 'Export terminé avec succès.', null, $url);

        } catch(\Throwable $t) {
            $this->logService->sendNotification($user, $notification, 'Une erreur est survenue durant l\'export.');
            $this->logService->critical($t->getMessage());
            $this->logService->critical($t->getTraceAsString());
        }
    }
}
