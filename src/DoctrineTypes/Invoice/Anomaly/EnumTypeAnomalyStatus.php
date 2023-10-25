<?php

declare(strict_types=1);

namespace App\DoctrineTypes\Invoice\Anomaly;

use App\DoctrineTypes\EnumType;
use App\Entity\Invoice\Anomaly;

class EnumTypeAnomalyStatus extends EnumType
{
    protected string $name = 'enumTypeAnomalyStatus';
    protected array $values = Anomaly::AVAILABLE_STATUS;
}
