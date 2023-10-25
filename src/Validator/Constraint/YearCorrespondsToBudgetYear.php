<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Entity\Pricing;
use Symfony\Component\Validator\Constraint;

class YearCorrespondsToBudgetYear extends Constraint
{
    public int $budgetId;
}