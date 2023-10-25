<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Budget\DeliveryPointBudget;
use App\Manager\Budget\DeliveryPointBudgetManager;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DeliveryPointBudgetNormalizer implements ContextAwareNormalizerInterface
{
    private DeliveryPointBudgetManager $deliveryPointBudgetManager;
    private ObjectNormalizer $normalizer;

    public function __construct(
        DeliveryPointBudgetManager $deliveryPointBudgetManager,
        ObjectNormalizer $normalizer
    ) {
        $this->deliveryPointBudgetManager = $deliveryPointBudgetManager;
        $this->normalizer = $normalizer;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \Symfony\Component\Serializer\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Serializer\Exception\LogicException
     */
    public function normalize($deliveryPointBudget, string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($deliveryPointBudget, $format, $context);

        $expands = $context['groups'] ?? [];

        if (in_array(DeliveryPointBudget::EXPAND_DATA_PREVIOUS_DELIVERY_POINT_BUDGET, $expands)) {
            $previousDpb = $this->deliveryPointBudgetManager->getPrevious($deliveryPointBudget);
            // remove previous deliveryPointBudget expand to make sure we aren't including the previous year + the previous of the previous etc
            $context['groups'] = array_diff($expands, [DeliveryPointBudget::EXPAND_DATA_PREVIOUS_DELIVERY_POINT_BUDGET]);
            $data['previous'] = $previousDpb ? $this->normalize($previousDpb, $format, $context) : null;
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof DeliveryPointBudget;
    }
}