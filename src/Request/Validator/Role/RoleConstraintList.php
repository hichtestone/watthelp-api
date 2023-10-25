<?php

declare(strict_types=1);

namespace App\Request\Validator\Role;

use App\Entity\Permission;
use App\Entity\User;
use App\Query\Criteria;
use App\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class RoleConstraintList extends Collection
{
    public function __construct(array $options = [])
    {
        $fields = [
            'name' => new Required([
                new Type(['type' => 'string']),
                new NotBlank()
            ]),
            'description' => new Optional([
                new Type(['type' => 'string'])
            ]),
            'users' => new Optional(new All([
                new NotBlank(),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\User\Id::class,
                    'entity' => User::class,
                    'notFoundMessage' => 'Selected user doesn\'t exist.'
                ])
            ])),
            'permissions' => new Optional(new All([
                new NotBlank(),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\Permission\Id::class,
                    'entity' => Permission::class,
                    'notFoundMessage' => 'Selected permission doesn\'t exist.'
                ])
            ]))
        ];

        parent::__construct($fields);
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}