<?php

declare(strict_types=1);

namespace App\Serializer\Denormalizer;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\BadMethodCallException;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

abstract class AbstractDenormalizer implements SerializerAwareInterface
{
    protected EntityManagerInterface $entityManager;
    protected $object;
    protected array $data;
    protected SerializerInterface $serializer;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * Denormalizes data back into an object of the given class.
     *
     * @param mixed  $data    Data to restore
     * @param string $type    The expected class to instantiate
     * @param string $format  Format the given data was extracted from
     * @param array  $context Options available to the denormalizer
     *
     * @throws BadMethodCallException   Occurs when the normalizer is not called in an expected context
     * @throws InvalidArgumentException Occurs when the arguments are not coherent or not supported
     * @throws UnexpectedValueException Occurs when the item cannot be hydrated with the given data
     * @throws ExtraAttributesException Occurs when the item doesn't have attribute to receive given data
     * @throws LogicException           Occurs when the normalizer is not supposed to denormalize
     * @throws RuntimeException         Occurs if the class cannot be instantiated
     * @throws ExceptionInterface       Occurs for all the other cases of errors
     *
     * @return object|array
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $this->data = $data;
        $this->object = $this->serializer->denormalize($data, DefaultDenormalizer::class, null, $context);

        return $this->object;
    }

    /**
     * Sets the owning Serializer object.
     */
    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    /**
     * @param bool $nullable
     *
     * @throws \Exception
     */
    public function handleDateTime(string $parameter, $nullable = true): void
    {
        $method = $this->formatParameterToSetter($parameter);

        if (\array_key_exists($parameter, $this->data)) {
            if ($nullable && empty($this->data[$parameter])) {
                $this->object->$method(null);
            } else {
                $this->object->$method(new \DateTime($this->data[$parameter]));
            }
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function handleFile(string $parameter, bool $nullable = true): void
    {
        $method = $this->formatParameterToSetter($parameter);

        if (\array_key_exists($parameter, $this->data)) {
            if ($nullable && empty($this->data[$parameter])) {
                $this->object->$method(null);
            } else {
                $this->object->$method($this->entityManager->getReference(File::class, (int) $this->data[$parameter]));
            }
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function handleFiles(iterable $parameters, bool $nullable = true): void
    {
        foreach ($parameters as $param) {
            $this->handleFile($param, $nullable);
        }
    }

    public function handleArray(string $parameter): void
    {
        $method = $this->formatParameterToSetter($parameter);

        if (\array_key_exists($parameter, $this->data)) {
            $this->object->$method((array) $this->data[$parameter]);
        }
    }

    public function formatParameterToSetter(string $parameter): string
    {
        $converter = new CamelCaseToSnakeCaseNameConverter();

        return \sprintf('set%s', \ucfirst($converter->denormalize($parameter)));
    }
}