<?php

declare(strict_types=1);

namespace App\Request\Validator\User;

use App\Entity\File;
use App\Query\Criteria;
use App\Validator\Constraint\FileType;
use App\Validator\Constraint\SelectableGeneric;
use App\Validator\Constraint\XorPath;
use App\Validator\Constraint\XorPathValidator;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\IdenticalTo;

class OperationConstraintList extends XorPath
{
    use UserConstraintTrait;

    public function __construct(array $options = [])
    {
        $request = $options['request'] ?? null;
        $existingId = $request ? intval($request->attributes->get('id')) : null;

        $email = [
            'op' => new Required([
                new NotNull(),
                new IdenticalTo('replace'),
            ]),
            'path' => new Required([
                new NotNull(),
                new IdenticalTo('/email'),
            ]),
            'value' => new Required($this->getEmailConstraint($existingId)),
        ];

        $firstName = [
            'op' => new Required([
                new NotNull(),
                new IdenticalTo('replace'),
            ]),
            'path' => new Required([
                new NotNull(),
                new IdenticalTo('/firstName'),
            ]),
            'value' => new Required([
                new NotNull(),
                new Type(['type' => 'string'])
            ]),
        ];

        $lastName = [
            'op' => new Required([
                new NotNull(),
                new IdenticalTo('replace'),
            ]),
            'path' => new Required([
                new NotNull(),
                new IdenticalTo('/lastName'),
            ]),
            'value' => new Required([
                new NotNull(),
                new Type(['type' => 'string'])
            ]),
        ];

        $phone = [
            'op' => new Required([
                new NotNull(),
                new IdenticalTo('replace'),
            ]),
            'path' => new Required([
                new NotNull(),
                new IdenticalTo('/phone'),
            ]),
            'value' => new Required([
                new NotNull(),
                new Length(['max' => 20]),
                new Type(['type' => 'string'])
            ]),
        ];

        $mobile = [
            'op' => new Required([
                new NotNull(),
                new IdenticalTo('replace'),
            ]),
            'path' => new Required([
                new NotNull(),
                new IdenticalTo('/mobile'),
            ]),
            'value' => new Required([
                new NotNull(),
                new Length(['max' => 20]),
                new Type(['type' => 'string'])
            ]),
        ];

        $avatar = [
            'op' => new Required([
                new NotNull(),
                new IdenticalTo('replace'),
            ]),
            'path' => new Required([
                new NotNull(),
                new IdenticalTo('/avatar'),
            ]),
            'value' => [
                new SelectableGeneric([
                    'criteria' => Criteria\File\Id::class,
                    'entity' => File::class
                ]),
                new FileType([
                    'allowedMimeTypes' => ['image/png', 'image/jpg', 'image/jpeg'],
                    'errorMessage' => 'only_images_are_accepted',
                ])
            ]
        ];

        $fields = [
            new Collection($email),
            new Collection($firstName),
            new Collection($lastName),
            new Collection($mobile),
            new Collection($phone),
            new Collection($avatar)
        ];

        if (!empty($options['fields']) && \is_array($options['fields'])) {
            $fields = array_merge($fields, $options['fields']);
        }

        parent::__construct($fields);
    }

    public function validatedBy(): string
    {
        return XorPathValidator::class;
    }
}
