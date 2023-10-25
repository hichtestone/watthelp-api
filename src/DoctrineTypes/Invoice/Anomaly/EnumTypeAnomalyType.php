<?php

declare(strict_types=1);

namespace App\DoctrineTypes\Invoice\Anomaly;

use App\DoctrineTypes\EnumType;
use App\Entity\Invoice\Anomaly;

class EnumTypeAnomalyType extends EnumType
{
    protected string $name = 'enumTypeAnomalyType';
    protected array $values = Anomaly::AVAILABLE_TYPES;
}
