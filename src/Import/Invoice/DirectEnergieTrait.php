<?php

declare(strict_types=1);

namespace App\Import\Invoice;

use App\Entity\Contract;
use App\Service\SpreadsheetService;

trait DirectEnergieTrait
{
    private string $fileName = 'facture.xlsx';
    private string $provider = Contract::PROVIDER_DIRECT_ENERGIE;
    private int $firstRowIndex = 2;
    private SpreadsheetService $spreadsheetService;
    
    public function supports(?string $provider): bool
    {
        return $this->provider === $provider;
    }
}