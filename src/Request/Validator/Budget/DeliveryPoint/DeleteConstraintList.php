<?php

declare(strict_types=1);

namespace App\Request\Validator\Budget\DeliveryPoint;

use App\Entity\Budget\DeliveryPointBudget;
use App\Query\Criteria;
use App\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\IdenticalTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class DeleteConstraintList extends Collection
{
    public function __construct(array $options = [])
    {
        $budgetId = intval($options['request']->attributes->get('budget_id'));
        $fields = [
            'ids' => new Required(new AtLeastOneOf([
                new All([
                    new NotBlank(),
                    new Type(['type' => 'numeric']),
                    new Constraint\SelectableGeneric([
                        'criteria' => Criteria\Budget\DeliveryPoint\Id::class,
                        'entity' => DeliveryPointBudget::class,
                        'budgetId' => $budgetId
                    ])
                ]),
                new IdenticalTo('*')
            ])),
        ];

        parent::__construct(compact('fields'));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}