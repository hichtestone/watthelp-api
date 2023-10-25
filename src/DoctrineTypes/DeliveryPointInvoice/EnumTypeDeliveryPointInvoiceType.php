<?php

declare(strict_types=1);

namespace App\DoctrineTypes\DeliveryPointInvoice;

use App\DoctrineTypes\EnumType;
use App\Entity\Invoice\DeliveryPointInvoice;

class EnumTypeDeliveryPointInvoiceType extends EnumType
{
    protected string $name = 'enumTypeDeliveryPointInvoiceType';
    protected array $values = DeliveryPointInvoice::AVAILABLE_TYPES;
}