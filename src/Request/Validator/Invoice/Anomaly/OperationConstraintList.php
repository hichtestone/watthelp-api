<?php

declare(strict_types=1);

namespace App\Request\Validator\Invoice\Anomaly;

use App\Entity\Invoice\Anomaly;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\IdenticalTo;

class OperationConstraintList extends Collection
{
    public function __construct($options = null)
    {
        $operations = [
            'op' => new Required([
                new NotNull(),
                new IdenticalTo('replace')
            ]),
            'path' => new Required([
                new NotNull(),
                new IdenticalTo('/status'),
            ]),
            'value' => new Required([
                new Choice([
                    'strict' => true,
                    'choices' => Anomaly::AVAILABLE_STATUS
                ])
            ])
        ];

        $fields = [
            'operations' => new All([
                new Collection($operations)
            ])
        ];
        
        parent::__construct($fields);
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}