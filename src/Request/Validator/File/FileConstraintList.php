<?php

declare(strict_types=1);

namespace App\Request\Validator\File;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Required;

class FileConstraintList extends Collection
{
    public function __construct()
    {
        $fields = [
            'file' => new Required([
                new File([
                    'maxSize' => '10M'
                ]),
            ]),
        ];

        parent::__construct(compact('fields'));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}
