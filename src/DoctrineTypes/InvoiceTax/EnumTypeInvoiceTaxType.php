<?php

declare(strict_types=1);

namespace App\DoctrineTypes\InvoiceTax;

use App\DoctrineTypes\EnumType;
use App\Entity\Invoice\InvoiceTax;

class EnumTypeInvoiceTaxType extends EnumType
{
    protected string $name = 'enumTypeInvoiceTaxType';
    protected array $values = InvoiceTax::AVAILABLE_TYPES;
}