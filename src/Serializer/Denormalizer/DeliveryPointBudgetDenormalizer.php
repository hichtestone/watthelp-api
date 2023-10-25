<?php

declare(strict_types=1);

namespace App\Serializer\Denormalizer;

use App\Entity\Budget\DeliveryPointBudget;
use App\Entity\DeliveryPoint;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class DeliveryPointBudgetDenormalizer extends AbstractDenormalizer implements DenormalizerInterface
{
    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function denormalize($data, string $class, string $format = null, array $context = []): DeliveryPointBudget
    {
        $deliveryPointBudget = parent::denormalize($data, $class, $format, ['object_to_populate' => $context['object_to_populate'] ?? new DeliveryPointBudget()]);

        $this->handleDateTime('renovated_at');

        if (isset($data['delivery_point'])) {
            $deliveryPoint = $this->entityManager->getReference(DeliveryPoint::class, $data['delivery_point']);
            $deliveryPointBudget->setDeliveryPoint($deliveryPoint);            
        }

        return $deliveryPointBudget;
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return isset($data) && DeliveryPointBudget::class === $type;
    }
}