<?php

declare(strict_types=1);

namespace App\Request\Validator\Budget\DeliveryPoint;

use App\Entity\DeliveryPoint;
use App\Query\Criteria;
use App\Validator\Constraint;
use App\Validator\Constraint\YearCorrespondsToBudgetYear;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class DeliveryPointBudgetConstraintList extends Collection
{
    public function __construct(array $options = [])
    {
        $request = $options['request'] ?? null;
        $budgetId = $request ? intval($request->attributes->get('budget_id')) : 0;
        $fields = [
            'delivery_point' => new Required([
                new NotNull(),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\DeliveryPoint\Id::class,
                    'entity' => DeliveryPoint::class,
                    'notFoundMessage' => 'Selected delivery point doesn\'t exist.'
                ])
            ]),
            'installed_power' => new Required([
                new Type(['type' => 'numeric'])
            ]),
            'equipment_power_percentage' => new Required([
                new Type(['type' => 'integer'])
            ]),
            'gradation' => new Required([
                new Type(['type' => 'integer'])
            ]),
            'gradation_hours' => new Required([
                new Type(['type' => 'integer'])
            ]),
            'sub_total_consumption' => new Required([
                new Type(['type' => 'integer'])
            ]),
            'renovation' => new Required([
                new Type(['type' => 'boolean'])
            ]),
            'renovated_at' => new Required([
                new Date(),
                new YearCorrespondsToBudgetYear(['budgetId' => $budgetId])
            ]),
            'new_installed_power' => new Required([
                new Type(['type' => 'numeric'])
            ]),
            'new_equipment_power_percentage' => new Required([
                new Type(['type' => 'integer'])
            ]),
            'new_gradation' => new Required([
                new Type(['type' => 'integer'])
            ]),
            'new_gradation_hours' => new Required([
                new Type(['type' => 'integer'])
            ]),
            'new_sub_total_consumption' => new Required([
                new Type(['type' => 'integer'])
            ]),
            'total_consumption' => new Required([
                new Type(['type' => 'integer'])
            ]),
            'total' => new Required([
                new Type(['type' => 'integer'])
            ]),
        ];

        parent::__construct(compact('fields'));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}