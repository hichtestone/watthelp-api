<?php

declare(strict_types=1);

namespace App\Request\Validator\Invoice;

use App\Entity\Invoice;
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
            'reference',
            'amount_ht',
            'amount_tva',
            'amount_ttc',
            'emitted_at'
        ]));

        parent::__construct($sort, self::getFiltersConstraint());
    }

    public static function getFiltersConstraint(): Optional
    {
        $idConstraint = new Optional(new All([
            new NotBlank(),
            new Type(['type' => 'numeric']),
            new Constraint\SelectableGeneric([
                'criteria' => Criteria\Invoice\Id::class,
                'entity' => Invoice::class,
                'notFoundMessage' => 'Selected invoice doesn\'t exist.'
            ])
        ]));
        return new Optional(new Collection([
            'id' => $idConstraint,
            'exclude_ids' => $idConstraint,
            'reference' => new Optional(new NotBlank()),
            'has_analysis' => new Optional(new Choice(['0', '1']))
        ]));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}