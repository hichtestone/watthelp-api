<?php

declare(strict_types=1);

namespace App\Model\Import\Invoice;

use App\Entity\Pricing;
use \DateTimeInterface;

class ImportData
{
    public DateTimeInterface $invoiceDate;
    public string $invoiceReference;
    public string $deliveryPointReference;
    public ?string $deliveryPointInvoiceReference = null;
    public ?string $contractReference = null;
    public string $contractType = Pricing::TYPE_NEGOTIATED;
    public ?DateTimeInterface $contractFinishedDate = null;
    public ?string $deliveryPointName = null;
    public ?string $deliveryPointAddress = null;
    public ?string $deliveryPointZipCode = null;
    public ?string $deliveryPointCity = null;
    public ?string $powerSubscribed = null;
    public ?string $meterReference = null;
    public ?int $cspe = null;
    public ?int $tdcfe = null;
    public ?int $tccfe = null;
    public ?int $tcfe = null;
    public ?int $cta = null;
    public ?int $totalTaxExcluded = null;
    public ?int $totalTVA = null;
    public ?int $totalTaxIncluded = null;
    public ?DateTimeInterface $subscriptionStartedDate = null;
    public ?DateTimeInterface $subscriptionFinishedDate = null;
    public ?int $subscriptionQuantity = null;
    public ?int $subscriptionTotal = null;
    public ?int $turpe = null;
    public ?int $consumptionQuantity = null;
    public ?int $consumptionTotal = null;
    public ?int $consumptionUnitPrice = null;
    public ?int $consumptionIndexStart = null;
    public ?int $consumptionIndexFinish = null;
    public ?DateTimeInterface $consumptionStartedDate = null;
    public ?DateTimeInterface $consumptionFinishedDate = null;
    public ?DateTimeInterface $consumptionIndexStartedDate = null;
    public ?DateTimeInterface $consumptionIndexFinishedDate = null;
    public ?string $readingType = null;
}