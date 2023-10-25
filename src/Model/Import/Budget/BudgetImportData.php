<?php

declare(strict_types=1);

namespace App\Model\Import\Budget;

class BudgetImportData
{
    public int $year;
    public int $totalHours;
    public int $averagePrice;
    public ?int $totalConsumption;
    public ?int $totalAmount;
    public array $dpBudgets = [];
}