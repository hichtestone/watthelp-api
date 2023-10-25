<?php

declare(strict_types=1);

namespace App\Request\Validator\Budget;

use App\Entity\Budget;
use App\Query\Criteria;
use App\Request\Validator\AbstractFilterConstraintList;
use App\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;

class FilterConstraintList extends AbstractFilterConstraintList
{
    public function __construct(array $options = [])
    {
        $sort = new Optional(new Choice([
            'id',
            'year',
            'average_price',
            'total_hours',
            'total_consumption',
            'total_amount'
        ]));
        parent::__construct($sort, self::getFiltersConstraint());
    }

    public static function getFiltersConstraint(): Optional
    {
        return new Optional(new Collection([
            'year' => new Optional([
                new Type(['type' => 'numeric'])
            ]),
            'max_year' => new Optional([
                new Type(['type' => 'numeric'])
            ]),
            'ids' => new Optional(new All([
                new NotBlank(),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\Budget\Id::class,
                    'entity' => Budget::class,
                    'notFoundMessage' => 'Selected budget doesn\'t exist.'
                ])
            ]))
        ]));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}