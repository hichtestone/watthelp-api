<?php

declare(strict_types=1);

namespace App\Request\Validator\Notification;

use App\Request\Validator\AbstractFilterConstraintList;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\CollectionValidator;
use Symfony\Component\Validator\Constraints\Optional;

class FilterConstraintList extends AbstractFilterConstraintList
{
    public function __construct(array $options = null)
    {
        $sort = new Optional(new Choice([
            'id',
            'message',
            'url',
            'status',
            'created_at',
        ]));

        parent::__construct($sort, new Optional());
    }

    public function validatedBy(): string
    {
        return CollectionValidator::class;
    }
}
