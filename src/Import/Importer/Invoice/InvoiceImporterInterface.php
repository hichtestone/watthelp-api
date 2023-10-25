<?php

declare(strict_types=1);

namespace App\Import\Importer\Invoice;

use App\Entity\Client;

interface InvoiceImporterInterface
{
    public function import(string $filePath, Client $client, array $invoiceReferencesToReimport): array;
    public function supports(?string $provider): bool;
}