<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Entity\Client;
use App\Entity\HasClientInterface;

trait ValidatorHelperTrait
{
    private function initGetByCriteriaParameters(Client $client, string $entityName, object $repository): array
    {
        $criteriaParameters = [];
        
        if (is_subclass_of($entityName, HasClientInterface::class)) {
            $criteriaParameters[0] = $client;
        } else {
            $criteriaMethod = new \ReflectionMethod($repository, 'getByCriteria');
            $methodParameters = $criteriaMethod->getParameters();

            // check if first parameter is Client
            if (!empty($methodParameters) && $methodParameters[0]->getType()->getName() === Client::class) {
                $criteriaParameters[0] = $client;
            }
        }

        return $criteriaParameters;
    }
}