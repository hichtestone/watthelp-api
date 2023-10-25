<?php

declare(strict_types=1);

namespace App\Request\Validator;

use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\Existence;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Range;

class AbstractFilterConstraintList extends Collection
{
    public function __construct(Existence $sortConstraint, Existence $filtersConstraint)
    {
        $fields = [
            'page' => new Optional(
                new Range([
                    'min' => 1,
                ])
            ),
            'per_page' => new Optional(
                new Range([
                    'min' => 1,
                    'max' => 100,
                ])
            ),
            'sort_order' => new Optional(new Choice([
                'strict' => true,
                'choices' => [
                    'desc',
                    'asc',
                ],
            ])),
            'sort' => $sortConstraint,
            'filters' => $filtersConstraint
        ];

        parent::__construct($fields);
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}