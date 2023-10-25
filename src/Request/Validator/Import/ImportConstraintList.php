<?php

declare(strict_types=1);

namespace App\Request\Validator\Import;

use App\Entity\File;
use App\Query\Criteria;
use App\Validator\Constraint\FileType;
use App\Validator\Constraint\SelectableGeneric;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\Required;

class ImportConstraintList extends Collection
{

    public function __construct(array $options = [])
    {
        $fields = [
            'file' => new Required([
                new SelectableGeneric([
                    'criteria' => Criteria\File\Id::class,
                    'entity' => File::class,
                ]),
                new FileType([
                    'allowedMimeTypes' => [
                        'application/vnd.ms-excel',
                        'application/vnd.ms-office',
                        'application/xls',
                        'application/xlsx',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    ],
                    'errorMessage' => 'file_type_incorrect_expected_excel',
                ])
            ]),
        ];

        parent::__construct(compact('fields'));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}