<?php

declare(strict_types=1);

namespace App\Request\Validator\Invoice\Import;

use App\Entity\Contract;
use App\Entity\File;
use App\Entity\Invoice;
use App\Query\Criteria;
use App\Validator\Constraint;
use App\Validator\Constraint\IsFileTypeValidForProviderValidator;
use App\Validator\Constraint\SelectableGeneric;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;

class ImportConstraintList extends Collection
{

    public function __construct(array $options = [])
    {
        $request = $options['request'] ?? null;
        $requestData = $request ? $request->request->all() : [];

        $fields = [
            'provider' => new Required(
                new Choice([
                    'strict' => true,
                    'choices' => Contract::AVAILABLE_PROVIDERS
                ])
            ),
            'file' => new Required([
                new NotNull(),
                new SelectableGeneric([
                    'criteria' => Criteria\File\Id::class,
                    'entity' => File::class,
                ]),
                new Constraint\IsFileTypeValidForProvider(['file' => $requestData['file'] ?? 0, 'provider' => $requestData['provider'] ?? ''])
            ]),
            'reimport_invoices' => new Optional(new All([
                new NotBlank(),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\Invoice\Reference::class,
                    'entity' => Invoice::class,
                    'notFoundMessage' => 'Selected invoice doesn\'t exist.'
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
