<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

abstract class AbstractPatchController
{
    /**
     * @param $entity
     */
    protected function handlePatchRequest(array $operations, $entity, array $allowedProperty): void
    {
        $converter = new CamelCaseToSnakeCaseNameConverter();

        foreach ($operations as $operation) {
            $path = \str_replace('-', '_', trim($operation['path'], '/'));
            $property = $converter->denormalize($path);

            if (!in_array($property, $allowedProperty, true)) {
                throw new \LogicException('Property is not allowed.');
            }

            switch ($operation['op']) {
                case 'replace':
                    $overrideMethod = $operation['op'].\ucfirst($property);
                    $setMethod = 'set'.\ucfirst($property);
                    $isMethod = 'is'.\ucfirst($property);

                    if (method_exists($this, $overrideMethod)) { // Test if method is override
                        $this->$overrideMethod($operation['value']);
                    } elseif (method_exists($entity, $setMethod)) { // Test if entity has setter
                        $entity->$setMethod($operation['value']);
                    } elseif (method_exists($entity, $isMethod)) { // Test if entity has isser
                        $entity->$isMethod($operation['value']);
                    } else {
                        throw new \LogicException('Unable to patch. Property is not allowed');
                    }
                    break;
            }
        }
    }
}
