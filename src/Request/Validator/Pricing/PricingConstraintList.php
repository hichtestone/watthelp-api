<?php

declare(strict_types=1);

namespace App\Request\Validator\Pricing;

use App\Entity\Pricing;
use App\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class PricingConstraintList extends Collection
{
    public function __construct(array $options = [])
    {
        $fields = [
            'name' => new Required([
                new Type(['type' => 'string']),
                new NotBlank()
            ]),
            'type' => new Required([
                new Choice([
                    'choices' => Pricing::AVAILABLE_TYPES,
                ]),
                new Constraint\IsPricingTypeSameAsContractType([
                    'entity' => $options['entity']
                ])
            ]),
            'subscription_price' => new Optional([
                new Type(['type' => 'int']),
                new Range(['min' => 0])
            ]),
            'consumption_base_price' => new Required([
                new Type(['type' => 'int']),
                new Range(['min' => 0]),
                new NotBlank()
            ]),
            'started_at' => new Required([
                new Date(),
                new NotBlank()
            ]),
            'finished_at' => new Optional([
                new Date(),
                new NotBlank(),
                new Constraint\GreaterThanOrEqual(['propertyPath' => 'started_at'])
            ])
        ];

        parent::__construct(compact('fields'));
    }

    public function validatedBy(): string
    {
        return PricingConstraintListValidator::class;
    }
}