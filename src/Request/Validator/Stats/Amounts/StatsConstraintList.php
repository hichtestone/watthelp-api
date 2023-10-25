<?php

declare(strict_types=1);

namespace App\Request\Validator\Stats\Amounts;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Optional;

class StatsConstraintList extends Collection
{
    public function __construct()
    {
        $fields = [
            'start' => new Optional(new Date()),
            'end' => new Optional(new Date())
        ];

        parent::__construct($fields);
    }

    public function validatedBy(): string
    {
        return StatsConstraintListValidator::class;
    }
}