<?php

declare(strict_types=1);

namespace App\Request\Validator\DeliveryPoint;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;

class ExportConstraintList extends Collection
{
    public function __construct(array $options = [])
    {
        $fields = [
            'filters' => FilterConstraintList::getFiltersConstraint()
        ];

        parent::__construct($fields);
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}