<?php

declare(strict_types=1);

namespace App\Request\Validator\Invoice\Pdf;

use App\Entity\File;
use App\Query\Criteria;
use App\Validator\Constraint\FileType;
use App\Validator\Constraint\SelectableGeneric;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Sequentially;

class PdfConstraintList extends Collection
{

    public function __construct(array $options = [])
    {
        $fields = [
            'pdf' => new Required(new AtLeastOneOf([
                'constraints' => [
                    new Sequentially([
                        new SelectableGeneric([
                            'criteria' => Criteria\File\Id::class,
                            'entity' => File::class
                        ]),
                        new FileType([
                            'allowedMimeTypes' => ['application/pdf']
                        ])
                    ]),
                    new IsNull()
                ],
                'message' => 'must_be_null_or_existing_pdf',
                'includeInternalMessages' => false
            ]))
        ];

        parent::__construct($fields);
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}