<?php

declare(strict_types=1);

namespace App\DoctrineTypes\Invoice\Anomaly;

use App\DoctrineTypes\EnumType;
use App\Entity\Invoice\Anomaly;

class EnumAnomalyProfit extends EnumType
{
    protected string $name = 'enumAnomalyProfit';
    protected array $values = Anomaly::AVAILABLE_PROFITS;
}