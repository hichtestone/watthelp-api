<?php

declare(strict_types=1);

namespace App\Analyzer;

use App\Entity\Invoice\DeliveryPointInvoice;

interface AnalyzerInterface
{
    public const GROUP_CONSUMPTION = 'consumption';
    public const GROUP_SUBSCRIPTION = 'subscription';
    public const GROUP_TAX = 'tax';
    public const GROUP_DEFAULT = 'default';
    public const GROUP_INVOICE = 'invoice';

    public function analyze(DeliveryPointInvoice $deliveryPointInvoice): void;

    public function supportsAnalysis(DeliveryPointInvoice $deliveryPointInvoice): bool;

    public function getName(): string;

    public function getGroup(): string;

    public function stopChain(): bool;

    public function getPriority(): int;
}