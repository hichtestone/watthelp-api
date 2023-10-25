<?php

declare(strict_types=1);

namespace App\Request\Validator\Invoice\Analysis;

use App\Request\Validator\Invoice\FilterConstraintList;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;

class AnalyzeInvoicesConstraintList extends Collection
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