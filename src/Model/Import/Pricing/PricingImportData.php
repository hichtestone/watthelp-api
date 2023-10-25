<?php

declare(strict_types=1);

namespace App\Model\Import\Pricing;


class PricingImportData
{
    public string $name;
    public string $type;
    public \DateTimeInterface $startedAt;
    public \DateTimeInterface $finishedAt;
    public ?int $consumptionBasePrice = null;
    public ?int $subscriptionPrice = null;
}
