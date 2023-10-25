<?php

declare(strict_types=1);

namespace App\DoctrineTypes\Pricing;

use App\DoctrineTypes\EnumType;
use App\Entity\Pricing;

class EnumTypeType extends EnumType
{
    protected string $name = 'enumTypeType';
    protected array $values = Pricing::AVAILABLE_TYPES;
}