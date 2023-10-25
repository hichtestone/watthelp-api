<?php

declare(strict_types=1);

namespace App\Request\Validator\User;

use App\Entity\File;
use App\Entity\User;
use App\Entity\Role;
use App\Query\Criteria;
use App\Validator\Constraint\SelectableGeneric;
use App\Validator\Constraint\User\PasswordSecurity;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class PostConstraintList extends Collection
{
    use UserConstraintTrait;

    public function __construct()
    {
        $fields = [
            'email' => new Required($this->getEmailConstraint()),
            'password' => new Required([
                new NotBlank(),
                new PasswordSecurity()
            ]),
            'first_name' => new Required([
                new NotBlank()
            ]),
            'last_name' => new Required([
                new NotBlank()
            ]),
            'phone' => new Optional([
                new Length(['max' => 20]),
                new Type(['type' => 'string'])
            ]),
            'mobile' => new Optional([
                new Length(['max' => 20]),
                new Type(['type' => 'string'])
            ]),
            'avatar' => new Optional([
                new SelectableGeneric([
                    'criteria' => Criteria\File\Id::class,
                    'entity' => File::class
                ])
            ]),
            'roles' => new Optional(new All([
                new NotBlank(),
                new Type(['type' => 'numeric']),
                new SelectableGeneric([
                    'criteria' => Criteria\Role\Id::class,
                    'entity' => Role::class,
                    'notFoundMessage' => 'Selected role doesn\'t exist.'
                ])
            ])),
            'language' => new Optional(new Choice(User::AVAILABLE_LANGUAGES))
        ];

        parent::__construct(compact('fields'));
    }

    public function validatedBy(): string
    {
        return  CollectionValidator::class;
    }
}