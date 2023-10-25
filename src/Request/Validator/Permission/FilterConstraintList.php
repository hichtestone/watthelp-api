<?php

declare(strict_types=1);

namespace App\Request\Validator\Permission;

use App\Entity\Permission;
use App\Query\Criteria;
use App\Request\Validator\AbstractFilterConstraintList;
use App\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;

class FilterConstraintList extends AbstractFilterConstraintList
{
    public function __construct(array $options = [])
    {
        $sort = new Optional(new EqualTo('id'));

        $filters = new Optional(new Collection([
            'ids' => new Optional(new All([
                new NotBlank(),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\Permission\Id::class,
                    'entity' => Permission::class,
                    'notFoundMessage' => 'Selected permission doesn\'t exist.'
                ])
            ])),
            'codes' => new Optional(new All(
                new Choice(Permission::AVAILABLE_PERMISSION_CODES)
            ))
        ]));

        parent::__construct($sort, $filters);
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}