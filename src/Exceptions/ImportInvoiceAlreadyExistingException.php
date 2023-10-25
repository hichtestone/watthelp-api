<?php

declare(strict_types=1);

namespace App\Exceptions;

use \Throwable;

class ImportInvoiceAlreadyExistingException extends ImportException
{
    private array $invoices;

    public function __construct(array $invoices, array $errorMessages = [], int $code = 0, Throwable $previous = null)
    {
        $this->invoices = $invoices;
        parent::__construct($errorMessages, $code, $previous);
    }

    public function getInvoices(): array
    {
        return $this->invoices;
    }
}