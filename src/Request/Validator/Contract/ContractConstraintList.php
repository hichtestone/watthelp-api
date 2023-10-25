<?php

declare(strict_types=1);

namespace App\Request\Validator\Contract;

use App\Entity\Contract;
use App\Entity\Pricing;
use App\Query\Criteria;
use App\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class ContractConstraintList extends Collection
{
    public function __construct()
    {
        $fields = [
            'reference' => new Required([
                new Type(['type' => 'string']),
                new NotBlank()
            ]),
            'started_at' => new Required(new Date()),
            'finished_at' => new Optional([
                new Date(),
                new Constraint\GreaterThanOrEqual(['propertyPath' => 'started_at'])
            ]),
            'pricing_ids' => new Optional(new All([
                new NotBlank(),
                new Type(['type' => 'numeric']),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\Pricing\Id::class,
                    'entity' => Pricing::class,
                    'notFoundMessage' => 'Selected pricing doesn\'t exist.'
                ]),
            ])),
            'provider' => new Required(new Choice([
                'choices' => Contract::AVAILABLE_PROVIDERS
            ])),
            'type' => new Required(new Choice([
                'choices' => Pricing::AVAILABLE_TYPES
            ])),
            'invoice_period' => new Optional(new Choice([
                'choices' => Contract::AVAILABLE_INVOICE_PERIODS
            ]))
        ];

        parent::__construct(compact('fields'));
    }

    public function validatedBy(): string
    {
        return ContractConstraintListValidator::class;
    }
}
