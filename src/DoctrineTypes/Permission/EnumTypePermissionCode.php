<?php

declare(strict_types=1);

namespace App\DoctrineTypes\Permission;

use App\DoctrineTypes\EnumType;
use App\Entity\Permission;

class EnumTypePermissionCode extends EnumType
{
    protected string $name = 'enumTypePermissionCode';
    protected array $values = Permission::AVAILABLE_PERMISSION_CODES;
}