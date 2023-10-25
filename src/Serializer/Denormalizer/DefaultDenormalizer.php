<?php

declare(strict_types=1);

namespace App\Serializer\Denormalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class DefaultDenormalizer implements DenormalizerInterface
{
    public const AVAILABLE_CAST = [
        'string' => 'strval',
        'int' => 'intval',
        'bool' => 'boolval',
    ];

    /**
     * @param mixed  $data
     * @param string $class
     *
     * @throws \ReflectionException
     *
     * @return array|mixed|object
     */
    public function denormalize($data, string $class, string $format = null, array $context = [])
    {
        if (empty($context['object_to_populate'])) {
            throw new \LogicException('object_to_populate cannot empty');
        }

        $object = $context['object_to_populate'];

        foreach ($data as $key => $value) {
            if ($method = $this->getMethodSetter($object, $key)) {
                $parameterReflection = $this->getParameterType($object, $method);
                if (!$parameterReflection) {
                    throw new \LogicException(\sprintf('Object "%s" doesn\'t have type on method "%s"', \get_class($object), $method));
                }

                $type = $parameterReflection->getName();

                if (\array_key_exists($type, self::AVAILABLE_CAST)) {
                    // Allow set null value
                    if (null === $value && $parameterReflection->allowsNull()) {
                        $object->$method(null);
                    } else {
                        $castMethod = self::AVAILABLE_CAST[$type];

                        $object->$method($castMethod($value));
                    }
                }
            }
        }

        return $object;
    }

    /**
     * Checks whether the given class is supported for denormalization by this normalizer.
     *
     * @param mixed  $data   Data to denormalize from
     */
    public function supportsDenormalization($data, string $type, ?string $format = null): bool
    {
        return isset($data) && __CLASS__ === $type;
    }

    /**
     * Search setter method if exist and return this.
     *
     * @param $object
     */
    public function getMethodSetter($object, string $param): ?string
    {
        $converter = new CamelCaseToSnakeCaseNameConverter();
        $method = \sprintf('set%s', \ucfirst($converter->denormalize($param)));

        return method_exists($object, $method) ? $method : null;
    }

    /**
     * Get first parameter type of method.
     *
     * @param $object
     *
     * @throws \ReflectionException
     */
    public function getParameterType($object, string $method): ?\ReflectionType
    {
        $reflectionMethod = new \ReflectionMethod($object, $method);
        $parameters = $reflectionMethod->getParameters();

        if (empty($parameters)) {
            throw new \LogicException('No parameters are allowed on '.$method);
        }

        return $parameters[0]->getType();
    }
}