<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Notification;
use App\Manager\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

class NotificationService
{
    private PublisherInterface $publisher;
    private string $mercurePublishPath;
    private SerializerInterface $serializer;

    private NotificationManager $notificationManager;

    public function __construct(
        PublisherInterface $publisher,
        SerializerInterface $serializer,
        NotificationManager $notificationManager,
        string $mercurePublishPath
    )
    {
        $this->publisher = $publisher;
        $this->mercurePublishPath = $mercurePublishPath;
        $this->serializer = $serializer;
        $this->notificationManager = $notificationManager;
    }

    public function resetManager(EntityManagerInterface $entityManager): void
    {
        $this->notificationManager = new NotificationManager($entityManager);
    }

    /**
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateNotificationProgress(Notification $notification, bool $persist = true): Notification
    {
        if ($persist) {
            $this->notificationManager->update($notification);
        }
        $key = sha1((string)$notification->getUser()->getId());

        $update = new Update(
            "{$this->mercurePublishPath}/notification/$key",
            \json_encode(['notification' => $this->serializer->normalize($notification, 'json', ['groups' => ['default']])]));

        ($this->publisher)($update);

        return $notification;
    }
}
