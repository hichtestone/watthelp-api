<?php

declare(strict_types=1);

namespace App\Request\Validator\Contract;

use App\Entity\Contract;
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
            'reference',
            'provider',
            'type',
            'invoice_period',
            'started_at',
            'finished_at'
        ]));
        $filters = new Optional(new Collection([
            'id' => new Optional(new NotBlank()),
            'exclude_ids' => new Optional(new All([
                new NotBlank(),
                new Type(['type' => 'numeric']),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\Contract\Id::class,
                    'entity' => Contract::class,
                    'notFoundMessage' => 'Selected contract doesn\'t exist.',
                ]),
            ])),
            'reference' => new Optional(new NotBlank()),
            'provider' => new Optional(new NotBlank()),
            'type' => new Optional(new NotBlank()),
            'invoice_period' => new Optional(new NotBlank())
        ]));

        parent::__construct($sort, $filters);
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}
