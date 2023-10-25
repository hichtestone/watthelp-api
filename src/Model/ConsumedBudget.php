<?php

declare(strict_types=1);

namespace App\Model;

class ConsumedBudget
{
    private Year $budgetByMonth;
    private int $totalBudgetConsumed = 0;
    private int $totalHoursConsumed = 0;

    public function getBudgetByMonth(): Year
    {
        return $this->budgetByMonth;
    }

    public function getTotalBudgetConsumed(): int
    {
        return $this->totalBudgetConsumed;
    }

    public function getTotalHoursConsumed(): int
    {
        return $this->totalHoursConsumed;
    }

    public function setBudgetByMonth(Year $budgetByMonth): void
    {
        $this->budgetByMonth = $budgetByMonth;
    }

    public function setTotalBudgetConsumed(int $totalBudgetConsumed): void
    {
        $this->totalBudgetConsumed = $totalBudgetConsumed;
    }

    public function setTotalHoursConsumed(int $totalHoursConsumed): void
    {
        $this->totalHoursConsumed = $totalHoursConsumed;
    }

    public function addTotalBudgetConsumed(int $totalBudgetConsumed): void
    {
        $this->totalBudgetConsumed += $totalBudgetConsumed;
    }

    public function addTotalHoursConsumed(int $totalHoursConsumed): void
    {
        $this->totalHoursConsumed += $totalHoursConsumed;
    }
}