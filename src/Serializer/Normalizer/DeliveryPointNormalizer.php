<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\DeliveryPoint;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Loggable\Entity\LogEntry;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DeliveryPointNormalizer implements ContextAwareNormalizerInterface
{
    private ObjectNormalizer $normalizer;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ObjectNormalizer $normalizer,
        EntityManagerInterface $entityManager
    ) {
        $this->normalizer = $normalizer;
        $this->entityManager = $entityManager;
    }

    public function normalize($deliveryPoint, string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($deliveryPoint, $format, $context);

        $expands = $context['groups'] ?? [];
        if (in_array(DeliveryPoint::EXPAND_DATA_POWER_HISTORY, $expands)) {
            $logEntryRepo = $this->entityManager->getRepository(LogEntry::class);
            $logEntries = $logEntryRepo->getLogEntries($deliveryPoint);
            $powerHistory = [];
            foreach ($logEntries as $logEntry) {
                if (!isset(($logEntry->getData())['power'])) {
                    continue;
                }
                $powerHistory[] = [
                    'power' => ($logEntry->getData())['power'],
                    'at' => $logEntry->getLoggedAt()->format(\DateTimeInterface::ATOM)
                ];
            }
            $data['power_history'] = $powerHistory;
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof DeliveryPoint;
    }
}