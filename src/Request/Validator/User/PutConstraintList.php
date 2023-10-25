<?php

declare(strict_types=1);

namespace App\Request\Validator\User;

use App\Entity\File;
use App\Entity\Role;
use App\Query\Criteria;
use App\Validator\Constraint\SelectableGeneric;
use App\Validator\Constraint\User\PasswordSecurity;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class PutConstraintList extends Collection
{
    use UserConstraintTrait;

    public function __construct(array $options = [])
    {
        $request = $options['request'] ?? null;
        $existingId = $request ? intval($request->attributes->get('id')) : null;

        $fields = [
            'email' => new Required($this->getEmailConstraint($existingId)),
            'first_name' => new Required(new NotBlank()),
            'last_name' => new Required(new NotBlank()),
            'password' => new Optional([
                new NotBlank(),
                new PasswordSecurity()
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
            ]))
        ];

        parent::__construct(compact('fields'));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}
