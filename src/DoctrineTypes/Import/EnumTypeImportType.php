<?php

declare(strict_types=1);

namespace App\DoctrineTypes\Import;

use App\DoctrineTypes\EnumType;
use App\Entity\Import;

class EnumTypeImportType extends EnumType
{
    protected string $name = 'enumTypeImportType';
    protected array $values = Import::AVAILABLE_TYPES;
}