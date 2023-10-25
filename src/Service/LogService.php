<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class LogService implements LoggerInterface
{
    private NotificationService $notificationService;
    private LoggerInterface $logger;
    private int $count = 0;
    private int $currentProgress = 0;

    public function __construct(NotificationService $notificationService, LoggerInterface $logger)
    {
        $this->notificationService = $notificationService;
        $this->logger = $logger;
    }

    public function emergency($message, array $context = array())
    {
        $this->logger->emergency($message, $context);
    }

    public function alert($message, array $context = array())
    {
        $this->logger->alert($message, $context);
    }

    public function critical($message, array $context = array())
    {
        $this->logger->critical($message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->logger->error($message, $context);
    }

    public function warning($message, array $context = array())
    {
        $this->logger->warning($message, $context);
    }

    public function notice($message, array $context = array())
    {
        $this->logger->notice($message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->logger->info($message, $context);
    }

    public function initProgression(int $count): void
    {
        $this->count = $count;
        $this->currentProgress = 0;
    }

    public function sendNotification(User $user, ?Notification $notification, string $message, ?array $data = null, ?string $url = null, bool $progress = false): Notification
    {
        $notification ??= new Notification();
        $notification->setUser($user);
        $notification->setMessage($message);
        $notification->setData($data);
        $notification->setUrl($url);
        if ($progress && $this->count > 1) {
            $progression = intval(($this->currentProgress++/$this->count)*100) ?: 1;
            $notification->setProgress($progression);
        } else {
            $notification->setProgress(null);
        }

        $this->logger->info($message, ['data' => $data, 'url' => $url]);

        return $this->notificationService->updateNotificationProgress($notification);
    }

    public function resetNotificationManager(EntityManagerInterface $entityManager): void
    {
        $this->notificationService->resetManager($entityManager);
    }

    public function debug($message, array $context = array())
    {
        $this->logger->debug($message, $context);
    }

    public function log($level, $message, array $context = array())
    {
        $this->logger->log($level, $message, $context);
    }
}
