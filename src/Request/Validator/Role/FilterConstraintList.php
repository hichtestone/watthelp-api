<?php

declare(strict_types=1);

namespace App\Request\Validator\Role;

use App\Entity\Permission;
use App\Entity\Role;
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

class FilterConstraintList extends AbstractFilterConstraintList
{
    public function __construct(array $options = [])
    {
        $sort = new Optional(new Choice([
            'id',
            'name',
            'description',
            'created_at',
            'updated_at'
        ]));

        $roleIdsConstraint = new Optional(new All([
            new NotBlank(),
            new Constraint\SelectableGeneric([
                'criteria' => Criteria\Role\Id::class,
                'entity' => Role::class,
                'notFoundMessage' => 'Selected role doesn\'t exist.'
            ])
        ]));

        $filters = new Optional(new Collection([
            'name' => new Optional(new NotBlank()),
            'description' => new Optional(new NotBlank()),
            'ids' => $roleIdsConstraint,
            'exclude_ids' => $roleIdsConstraint,
            'users' => new Optional(new All([
                new NotBlank(),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\User\Id::class,
                    'entity' => User::class,
                    'notFoundMessage' => 'Selected user doesn\'t exist.'
                ])
            ])),
            'permissions' => new Optional(new All(
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