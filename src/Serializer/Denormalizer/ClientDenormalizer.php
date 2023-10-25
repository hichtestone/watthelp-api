<?php

declare(strict_types=1);

namespace App\Serializer\Denormalizer;

use App\Entity\Client;
use App\Entity\File;
use Doctrine\ORM\ORMException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ClientDenormalizer extends AbstractDenormalizer implements DenormalizerInterface
{
    /**
     * Denormalizes data back into an object of the given class.
     *
     * @param mixed  $data    Data to restore
     * @param string $class   The expected class to instantiate
     * @param string $format  Format the given data was extracted from
     * @param array  $context Options available to the denormalizer
     *
     * @throws ORMException
     */
    public function denormalize($data, string $class, string $format = null, array $context = []): Client
    {
        $client = parent::denormalize($data, $class, $format, ['object_to_populate' => $context['object_to_populate'] ?? new Client()]);

        if (array_key_exists('logo', $data) && $data['logo'] instanceof File) {
            $client->setLogo($data['logo']);
        } else {
            $this->handleFile('logo');
        }

        return $client;
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
    public function supportsDenormalization($data, string $type, ?string $format = null): bool
    {
        return isset($data) && Client::class === $type;
    }
}