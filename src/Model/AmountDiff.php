<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Invoice\Anomaly;

class AmountDiff
{
    private int $amount;
    private float $percentage;
    private string $profit;

    public function __construct(int $amount, float $percentage, string $profit = Anomaly::PROFIT_NONE)
    {
        $this->amount = $amount;
        $this->percentage = $percentage;
        $this->profit = $profit;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getPercentage(): float
    {
        return $this->percentage;
    }

    public function getProfit(): string
    {
        return $this->profit;
    }
}