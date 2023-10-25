<?php

declare(strict_types=1);

namespace App\Request\Validator\ImportReport;

use App\Entity\ImportReport;
use App\Request\Validator\AbstractFilterConstraintList;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Range;

class FilterConstraintList extends AbstractFilterConstraintList
{
    public function __construct()
    {
        $sort = new Optional(new Choice([
            'id',
            'created_at'
        ]));
        $filters = new Optional(new Collection([
            'id' => new Optional(new NotBlank()),
            'status' => new Optional(new Choice(ImportReport::AVAILABLE_STATUSES))
        ]));

        parent::__construct($sort, $filters);
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}