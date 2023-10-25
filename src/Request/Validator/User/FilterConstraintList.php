<?php

declare(strict_types=1);

namespace App\Request\Validator\User;

use App\Entity\User;
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
            'first_name',
            'last_name',
            'email',
            'phone',
            'mobile'
        ]));
        $filters = new Optional(new Collection([
            'id' => new Optional(new All([new NotBlank()])),
            'name' => new Optional(new NotBlank()),
            'first_name' => new Optional(new NotBlank()),
            'last_name' => new Optional(new NotBlank()),
            'email' => new Optional(new NotBlank()),
            'phone' => new Optional(new NotBlank()),
            'mobile' => new Optional(new NotBlank()),
            'exclude_ids' => new Optional(new All([
                new NotBlank(),
                new Type(['type' => 'numeric']),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\User\Id::class,
                    'entity' => User::class,
                    'notFoundMessage' => 'Selected user doesn\'t exist.',
                ]),
            ])),
        ]));

        parent::__construct($sort, $filters);
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}
