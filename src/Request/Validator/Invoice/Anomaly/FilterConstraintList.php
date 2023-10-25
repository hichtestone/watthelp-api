<?php

declare(strict_types=1);

namespace App\Request\Validator\Invoice\Anomaly;

use App\Entity\Invoice;
use App\Entity\Invoice\Anomaly;
use App\Query\Criteria;
use App\Request\Validator\AbstractFilterConstraintList;
use App\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class FilterConstraintList extends AbstractFilterConstraintList
{
    public function __construct()
    {
        $sort = new Optional(new Choice([
            'id',
            'invoices',
            'content',
            'status',
            'total',
            'total_percentage',
            'created_at'
        ]));
        $filters = self::getFiltersConstraint();

        parent::__construct($sort, $filters);
    }

    public static function getFiltersConstraint(): Optional
    {
        return new Optional(new Collection([
            'id' => new Optional(new All([
                new NotBlank(),
                new Type(['type' => 'numeric']),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\Invoice\Anomaly\Id::class,
                    'entity' => Anomaly::class,
                    'notFoundMessage' => 'Selected anomaly doesn\'t exist.'
                ]),
            ])),
            'invoices' => new Optional(new All([
                new NotBlank(),
                new Type(['type' => 'numeric']),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\Invoice\Id::class,
                    'entity' => Invoice::class,
                    'notFoundMessage' => 'Selected invoice doesn\'t exist.'
                ]),
            ])),
            'invoice_reference' => new Optional([
                new NotBlank()
            ]),
            'status' => new Optional([
                new Choice([
                    'choices' => Anomaly::AVAILABLE_STATUS,
                ])
            ]),
            'created' => new Optional(new Collection([
                'from' => new Required(new Date()),
                'to' => new Required(new Date())
            ])),
            'total' => new Optional(new Positive()),
            'total_percentage' => new Optional(new Positive()),
            'profit' => new Optional(new Choice(Anomaly::AVAILABLE_PROFITS))
        ]));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}
