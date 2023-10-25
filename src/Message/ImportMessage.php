<?php

declare(strict_types=1);

namespace App\Message;

class ImportMessage
{
    private int $importId;
    private array $invoiceReferences;

    public function __construct(int $importId, array $invoiceReferences = [])
    {
        $this->importId = $importId;
        $this->invoiceReferences = $invoiceReferences;
    }

    public function getImportId(): int
    {
        return $this->importId;
    }

    public function getInvoiceReferences(): array
    {
        return $this->invoiceReferences;
    }
}