<?php

declare(strict_types=1);

namespace App\DoctrineTypes\Contract;

use App\DoctrineTypes\EnumType;
use App\Entity\Contract;

class EnumTypeInvoicePeriod extends EnumType
{
    protected string $name = 'enumInvoicePeriod';
    protected array $values = Contract::AVAILABLE_INVOICE_PERIODS;
}
