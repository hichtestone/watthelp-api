<?php

declare(strict_types=1);

namespace App\DoctrineTypes\ImportReport;

use App\DoctrineTypes\EnumType;
use App\Entity\ImportReport;

class EnumTypeImportReportStatus extends EnumType
{
    protected string $name = 'enumTypeImportReportStatus';
    protected array $values = ImportReport::AVAILABLE_STATUSES;
}