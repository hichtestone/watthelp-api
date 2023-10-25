<?php

declare(strict_types=1);

namespace App\Request\Validator\Budget;

use App\Entity\Budget;
use App\Query\Criteria;
use App\Validator\Constraint\Unique;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class BudgetPostConstraintList extends Collection
{
    public function __construct(array $options = [])
    {
        $fields = [
            'year' => new Required([
                new Type([
                    'type' => 'integer'
                ]),
                new Unique([
                    'class' => Budget::class,
                    'criteria' => Criteria\Budget\Year::class,
                    'errorMessage' => 'budget_of_year_already_exists'
                ])
            ])
        ];

        parent::__construct(compact('fields'));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}