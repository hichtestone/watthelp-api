<?php

declare(strict_types=1);

namespace App\Serializer\Denormalizer;

use App\Entity\DeliveryPoint;
use App\Entity\Contract;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class DeliveryPointDenormalizer extends AbstractDenormalizer implements DenormalizerInterface
{
    /**
     * @inheritDoc
     * @throws \Doctrine\ORM\ORMException
     */
    public function denormalize($data, string $class, string $format = null, array $context = []): DeliveryPoint
    {
        /** @var DeliveryPoint $deliveryPoint */
        $deliveryPoint = parent::denormalize($data, $class, $format, ['object_to_populate' => $context['object_to_populate'] ?? new DeliveryPoint()]);

        $contract = $this->entityManager->getReference(Contract::class, $data['contract']);
        $deliveryPoint->setContract($contract);
        $this->handleFile('photo');

        return $deliveryPoint;
    }

    /**
     * Checks whether the given class is supported for denormalization by this normalizer.
     *
     * @param mixed  $data   Data to denormalize from
     * @param string $type   The class to which the data should be denormalized
     * @param string $format The format being deserialized from
     *
     * @return bool
     */
    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return isset($data) && DeliveryPoint::class === $type;
    }
}
