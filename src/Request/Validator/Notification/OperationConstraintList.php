<?php

declare(strict_types=1);

namespace App\Request\Validator\Notification;

use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\IdenticalTo;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class OperationConstraintList extends Collection
{
    public function __construct(array $options = null)
    {
        /*
         * Enabled operation
        */
        $status = [
            'op' => new Required([
                new NotNull(),
                new IdenticalTo('replace'),
            ]),
            'path' => new Required([
                new NotNull(),
                new IdenticalTo('/read'),
            ]),
            'value' => new Required(
                new Type(['type' => 'bool'])
            ),
        ];

        $fields = [
            'operations' => new All([
                new Collection($status),
            ]),
        ];

        parent::__construct(compact('fields'));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}
