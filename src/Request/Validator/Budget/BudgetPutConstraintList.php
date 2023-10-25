<?php

declare(strict_types=1);

namespace App\Request\Validator\Budget;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class BudgetPutConstraintList extends Collection
{
    public function __construct(array $options = [])
    {
        $fields = [
            'average_price' => new Required([
                new Type([
                    'type' => 'integer'
                ])
            ]),
            'total_hours' => new Required([
                new Type([
                    'type' => 'integer'
                ])
            ]),
            'total_consumption' => new Optional([
                new Type([
                    'type' => 'integer'
                ])
            ]),
            'total_amount' => new Optional([
                new Type([
                    'type' => 'integer'
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