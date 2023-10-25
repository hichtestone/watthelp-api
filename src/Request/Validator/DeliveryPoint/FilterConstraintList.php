<?php

declare(strict_types=1);

namespace App\Request\Validator\DeliveryPoint;

use App\Entity\Contract;
use App\Entity\DeliveryPoint;
use App\Query\Criteria;
use App\Request\Validator\AbstractFilterConstraintList;
use App\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Type;

class FilterConstraintList extends AbstractFilterConstraintList
{
    public function __construct()
    {
        $sort = new Optional(new Choice([
            'id',
            'reference',
            'contract',
            'code',
            'scope_date'
        ]));
        $filters = self::getFiltersConstraint();

        parent::__construct($sort, $filters);
    }

    public static function getRawFilters(bool $includeIdConstraints = true): array
    {
        $filters = [
            'reference' => new Optional(new NotBlank()),
            'contract' => new Optional([
                new NotBlank(),
                new Type(['type' => 'numeric']),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\Contract\Id::class,
                    'entity' => Contract::class,
                    'notFoundMessage' => 'Selected contract doesn\'t exist.'
                ])
            ]),
            'code' => new Optional(new NotBlank()),
            'no_invoice_for_months' => new Optional(new Positive()),
            'is_in_scope' => new Optional(new Choice(['0', '1', 0, 1, true, false]))
        ];
        if ($includeIdConstraints) {
            $idConstraint = new Optional(new All([
                new NotBlank(),
                new Type(['type' => 'numeric']),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\DeliveryPoint\Id::class,
                    'entity' => DeliveryPoint::class,
                    'notFoundMessage' => 'Selected delivery point doesn\'t exist.',
                ])
            ]));
            $filters['ids'] = $idConstraint;
            $filters['exclude_ids'] = $idConstraint;
        }
        
        return $filters;        
    }

    public static function getFiltersConstraint(): Optional
    {
        return new Optional(new Collection(self::getRawFilters()));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}