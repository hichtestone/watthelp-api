<?php

declare(strict_types=1);

namespace App\Request\Validator\Budget\DeliveryPoint;

use App\Entity\Budget;
use App\Entity\DeliveryPoint;
use App\Query\Criteria;
use App\Request\Validator\AbstractFilterConstraintList;
use App\Request\Validator\DeliveryPoint\FilterConstraintList as DeliveryPointFilterConstraintList;
use App\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class FilterConstraintList extends AbstractFilterConstraintList
{
    public function __construct()
    {
        $sort = new Optional(new Choice(['id', 'reference', 'code', 'contract', 'scope_date']));
        $filters = [
            'budget' => new Required(
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\Budget\Id::class,
                    'entity' => Budget::class,
                    'notFoundMessage' => 'Selected budget doesn\'t exist.'
                ])
            ),
            'year' => new Optional([
                new Type(['type' => 'numeric'])
            ]),
            'delivery_point' => new Optional([
                new NotBlank(),
                new Type(['type' => 'numeric']),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\DeliveryPoint\Id::class,
                    'entity' => DeliveryPoint::class,
                    'notFoundMessage' => 'Selected delivery point doesn\'t exist.'
                ])
            ])
        ];
        // add delivery point filters except ids and exclude_ids
        $filters = new Required(new Collection(array_merge($filters, DeliveryPointFilterConstraintList::getRawFilters(false))));

        parent::__construct($sort, $filters);
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}