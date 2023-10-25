<?php

declare(strict_types=1);

namespace App\Request\Validator\User\Me;

use App\Entity\User;
use App\Validator\Constraint\XorPath;
use App\Validator\Constraint\XorPathValidator;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\IdenticalTo;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class UserMeConstraintList extends XorPath
{
    public function __construct($options = null)
    {
        $dashboard = new Collection([
            'op' => new Required([
                new NotNull(),
                new IdenticalTo('replace'),
            ]),
            'path' => new Required([
                new NotNull(),
                new IdenticalTo('/dashboard'),
            ]),
            'value' => new Optional(new Type(['type' => 'array']))
        ]);
        $language = new Collection([
            'op' => new Required([
                new NotNull(),
                new IdenticalTo('replace'),
            ]),
            'path' => new Required([
                new NotNull(),
                new IdenticalTo('/language'),
            ]),
            'value' => new Required(new Choice(User::AVAILABLE_LANGUAGES))
        ]);

        $fields = [
            $dashboard,
            $language
        ];

        parent::__construct($fields);
    }

    public function validatedBy(): string
    {
        return XorPathValidator::class;
    }
}