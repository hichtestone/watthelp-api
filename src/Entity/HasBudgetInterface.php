<?php

declare(strict_types=1);

namespace App\Entity;

interface HasBudgetInterface
{
    public function getBudget(): Budget;
}