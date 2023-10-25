<?php

declare(strict_types=1);

namespace App\Request\Validator\Pricing;

use App\Entity\Pricing;
use App\Query\Criteria;
use App\Request\Validator\AbstractFilterConstraintList;
use App\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class FilterConstraintList extends AbstractFilterConstraintList
{
    public function __construct()
    {
        $sort = new Optional(new Choice([
            'id',
            'name',
            'type',
            'started_at',
            'finished_at'
        ]));
        $filters = self::getFiltersConstraint();

        parent::__construct($sort, $filters);
    }

    public static function getFiltersConstraint(): Optional
    {
        return new Optional(new Collection([
            'id' => new Optional(new All([new NotBlank()])),
            'exclude_ids' => new Optional(new All([
                new NotBlank(),
                new Type(['type' => 'numeric']),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\Pricing\Id::class,
                    'entity' => Pricing::class,
                    'notFoundMessage' => 'Selected pricing doesn\'t exist.',
                ]),
            ])),
            'name' => new Optional(new NotBlank()),
            'excluded_periods' => new Optional(new All(new Collection([
                'started_at' => new Required(new Date()),
                'finished_at' => new Optional(new Date())
            ]))),
            'type' => new Optional(new Choice([
                'choices' => Pricing::AVAILABLE_TYPES
            ])),
            'enabled' => new Optional(new Choice([
                'choices' => ['0', '1']
            ]))
        ]));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}
