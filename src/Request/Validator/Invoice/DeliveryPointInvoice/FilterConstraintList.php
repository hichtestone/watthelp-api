<?php

declare(strict_types=1);

namespace App\Request\Validator\Invoice\DeliveryPointInvoice;

use App\Entity\Invoice\DeliveryPointInvoice;
use App\Query\Criteria;
use App\Request\Validator\AbstractFilterConstraintList;
use App\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;

class FilterConstraintList extends AbstractFilterConstraintList
{
    public function __construct()
    {
        $sort = new Optional(new Choice([
            'id',
            'amount_ht',
            'amount_tva',
            'amount_ttc',
            'emitted_at'
        ]));

        parent::__construct($sort, self::getFiltersConstraint());
    }

    public static function getFiltersConstraint(): Optional
    {
        return new Optional(new Collection([
            'ids' => new Optional(new All([
                new NotBlank(),
                new Type(['type' => 'numeric']),
                new Constraint\SelectableGeneric([
                    'criteria' => Criteria\Invoice\DeliveryPointInvoice\Id::class,
                    'entity' => DeliveryPointInvoice::class,
                    'notFoundMessage' => 'Selected delivery point invoice doesn\'t exist.',
                ])
            ])),
            'delivery_point_reference' => new Optional(new NotBlank()),
            'delivery_point_name' => new Optional(new NotBlank()),
            'invoice_reference' => new Optional(new NotBlank())
        ]));
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}