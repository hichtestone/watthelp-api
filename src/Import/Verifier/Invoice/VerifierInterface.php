<?php

declare(strict_types=1);

namespace App\Import\Verifier\Invoice;

use App\Entity\Client;

interface VerifierInterface
{
    public function verify(string $filePath, array $invoiceReferencesToReimport, Client $client): void;
    public function supports(?string $provider): bool;
}