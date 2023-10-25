<?php

declare(strict_types=1);

namespace App\Request\Validator\Invoice\Analysis;

use App\Entity\Invoice;
use App\Entity\Invoice\Analysis;
use App\Query\Criteria;
use App\Request\Validator\AbstractFilterConstraintList;
use App\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;

class FilterConstraintList extends AbstractFilterConstraintList
{
    public function __construct()
    {
        $sort = new Optional(new Choice([
            'id',
            'invoices',
            'status',
            'created_at',
        ]));
        $filters = self::getFiltersConstraint();

        parent::__construct($sort, $filters);
    }

    public static function getFiltersConstraint(): Optional
    {
        return new Optional(new Collection([
            'ids' => new Optional(new All([
                new NotBlank(),
                new Type(['type' => 'numeric']),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\Invoice\Analysis\Id::class,
                    'entity' => Analysis::class,
                    'notFoundMessage' => 'Selected analysis doesn\'t exist.'
                ])
            ])),
            'status' => new Optional(new Choice([
                'choices' => Analysis::AVAILABLE_STATUSES
            ])),
            'invoices' => new Optional(new All([
                new NotBlank(),
                new Type(['type' => 'numeric']),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\Invoice\Id::class,
                    'entity' => Invoice::class,
                    'notFoundMessage' => 'Selected invoice doesn\'t exist.'
                ])
            ]))
        ]));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}