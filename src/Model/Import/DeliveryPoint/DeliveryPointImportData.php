<?php

declare(strict_types=1);

namespace App\Model\Import\DeliveryPoint;

use \DateTimeInterface;

class DeliveryPointImportData
{
    public string $name;
    public string $reference;
    public ?string $code = null;
    public string $address;
    public ?string $latitude = null;
    public ?string $longitude = null;
    public string $meterReference;
    public string $power;
    public ?string $description;
    public bool $isInScope;
    public string $isInScopeRaw = '';
    public DateTimeInterface $scopeDate;
}