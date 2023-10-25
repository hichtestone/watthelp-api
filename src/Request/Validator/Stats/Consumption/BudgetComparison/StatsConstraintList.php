<?php

declare(strict_types=1);

namespace App\Request\Validator\Stats\Consumption\BudgetComparison;

use App\Request\Validator\DeliveryPoint\FilterConstraintList;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class StatsConstraintList extends Collection
{
    public function __construct()
    {
        $fields = [
            'delivery_point_filters' => FilterConstraintList::getFiltersConstraint(),
            'period' => new Required(new Collection([
                'start' => new Required(new Date()),
                'end' => new Required(new Date())
            ]))
        ];

        parent::__construct($fields);
    }

    public function validatedBy(): string
    {
        return StatsConstraintListValidator::class;
    }
}