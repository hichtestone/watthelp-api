<?php

declare(strict_types=1);

namespace App\Serializer\Denormalizer;

use App\Entity\Contract;
use App\Entity\Pricing;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ContractDenormalizer extends AbstractDenormalizer implements DenormalizerInterface
{
    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function denormalize($data, string $class, string $format = null, array $context = []): Contract
    {
        $contract = parent::denormalize($data, $class, $format, ['object_to_populate' => $context['object_to_populate'] ?? new Contract()]);

        $this->handleDateTime('started_at');
        $this->handleDateTime('finished_at');

        if (array_key_exists('pricing_ids', $data)) {
            $pricings = new ArrayCollection();
            foreach ($data['pricing_ids'] as $index => $pricingId) {
                $pricings->add($this->entityManager->getReference(Pricing::class, $pricingId));
            }
            $contract->setPricings($pricings);
        }

        return $contract;
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
        return isset($data) && Contract::class === $type;
    }
}
