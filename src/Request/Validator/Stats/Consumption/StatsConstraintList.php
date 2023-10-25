<?php

declare(strict_types=1);

namespace App\Request\Validator\Stats\Consumption;

use App\Request\Validator\DeliveryPoint\FilterConstraintList;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class StatsConstraintList extends Collection
{
    public function __construct()
    {
        $dayConstraint = new Required(new Range(['min' => 1, 'max' => 31]));
        $monthConstraint = new Required(new Range(['min' => 1, 'max' => 12]));
        $fields = [
            'years' => new Required(new All([
                new NotBlank(),
                new Type(['type' => 'numeric'])
            ])),
            'delivery_point_filters' => FilterConstraintList::getFiltersConstraint(),
            'period' => new Optional(new Collection([
                'start_day' => $dayConstraint,
                'start_month' => $monthConstraint,
                'end_day' => $dayConstraint,
                'end_month' => $monthConstraint
            ]))
        ];

        parent::__construct($fields);
    }

    public function validatedBy(): string
    {
        return StatsConstraintListValidator::class;
    }
}