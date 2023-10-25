<?php

declare(strict_types=1);

namespace App\Model\Import\Budget;

class DeliveryPointBudgetImportData
{
    public int $year;
    public string $dpRef;
    public ?int $total;
    public ?int $totalConsumption;
    public ?string $installedPower;
    public ?int $equipmentPowerPercentage;
    public ?int $gradation;
    public ?int $gradationHours;
    public ?int $subTotalConsumption;
    public ?string $renovationRaw;
    public bool $renovation = false;
    public ?\DateTimeInterface $renovatedAt;
    public ?string $newInstalledPower;
    public ?int $newEquipmentPowerPercentage;
    public ?int $newGradation;
    public ?int $newGradationHours;
    public ?int $newSubTotalConsumption;
}