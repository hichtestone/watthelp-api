<?php

declare(strict_types=1);

namespace App\Request\Validator\Invoice\Anomaly\Note;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class NoteConstraintList extends Collection
{
    public function __construct()
    {
        $fields = [
            'content' => new Required([
                new Type(['type' => 'string']),
                new NotBlank()
            ])
        ];

        parent::__construct(compact('fields'));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}
