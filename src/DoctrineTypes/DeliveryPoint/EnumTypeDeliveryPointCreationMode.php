<?php

declare(strict_types=1);

namespace App\DoctrineTypes\DeliveryPoint;

use App\DoctrineTypes\EnumType;
use App\Entity\DeliveryPoint;

class EnumTypeDeliveryPointCreationMode extends EnumType
{
    protected string $name = 'enumTypeDeliveryPointCreationMode';
    protected array $values = DeliveryPoint::AVAILABLE_CREATION_MODES;
}