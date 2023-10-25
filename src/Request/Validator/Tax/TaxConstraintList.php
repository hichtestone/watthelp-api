<?php

declare(strict_types=1);

namespace App\Request\Validator\Tax;

use App\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class TaxConstraintList extends Collection
{
    public function __construct()
    {
        $fields = [
            'cspe' => new Required([
                new Type(['type' => 'int']),
                new Range(['min' => 0]),
                new NotBlank()
            ]),
            'tdcfe' => new Required([
                new Type(['type' => 'int']),
                new Range(['min' => 0]),
                new NotBlank(),
            ]),
            'tccfe' => new Required([
                new Type(['type' => 'int']),
                new Range(['min' => 0]),
                new NotBlank()
            ]),
            'cta' => new Required([
                new Type(['type' => 'int']),
                new Range(['min' => 0]),
                new NotBlank()
            ]),
            'started_at' => new Required([
                new Date(),
                new NotBlank()
            ]),
            'finished_at' => new Required([
                new Date(),
                new NotBlank(),
                new Constraint\GreaterThanOrEqual(['propertyPath' => 'started_at'])
            ])
        ];

        parent::__construct(compact('fields'));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}