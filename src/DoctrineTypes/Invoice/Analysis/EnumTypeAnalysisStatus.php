<?php

declare(strict_types=1);

namespace App\DoctrineTypes\Invoice\Analysis;

use App\DoctrineTypes\EnumType;
use App\Entity\Invoice\Analysis;

class EnumTypeAnalysisStatus extends EnumType
{
    protected string $name = 'enumTypeAnalysisStatus';
    protected array $values = Analysis::AVAILABLE_STATUSES;
}