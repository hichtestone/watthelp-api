<?php

declare(strict_types=1);

namespace App\DoctrineTypes\Contract;

use App\DoctrineTypes\EnumType;
use App\Entity\Contract;

class EnumTypeProvider extends EnumType
{
    protected string $name = 'enumTypeProvider';
    protected array $values = Contract::AVAILABLE_PROVIDERS;
}