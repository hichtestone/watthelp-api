<?php

declare(strict_types=1);

namespace App\Request\Validator\Stats\Budget;

use App\Entity\Budget;
use App\Query\Criteria;
use App\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class StatsConstraintList extends Collection
{
    public function __construct()
    {
        $fields = [
            'year' => new Required([
                new NotBlank(),
                new Type(['type' => 'numeric']),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\Budget\Year::class,
                    'entity' => Budget::class,
                    'notFoundMessage' => 'budget_of_year_does_not_exist'
                ])
            ])
        ];

        parent::__construct(compact('fields'));
    }

    public function validatedBy(): string
    {
        return StatsConstraintListValidator::class;
    }
}