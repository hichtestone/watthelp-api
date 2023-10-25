<?php

declare(strict_types=1);

namespace App\Import\Invoice;

use App\Entity\Contract;
use App\Exceptions\InvalidFileException;
use App\Import\ExtractedFile;
use App\Service\SpreadsheetService;
use App\Service\ZipService;

trait EngieTrait
{
    private int $firstRowIndex = 14;
    private string $provider = Contract::PROVIDER_ENGIE;
    private string $invoiceFile = 'facture.xlsx';
    private string $indexFile = 'index.xlsx';
    private array $requiredFiles = [];
    private ZipService $zipService;
    private SpreadsheetService $spreadsheetService;

    
    public function supports(?string $provider): bool
    {
        return $this->provider === $provider;
    }

    /**
     * @throws InvalidFileException
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function extract(string $zipPath): array
    {
        $extractedFiles = $this->zipService->extract($zipPath, $this->requiredFiles);
        $extractedFiles[$this->invoiceFile] = new ExtractedFile($extractedFiles[$this->invoiceFile], $this->spreadsheetService->makeXslxSheet($extractedFiles[$this->invoiceFile], 'C5'));
        $extractedFiles[$this->indexFile] = new ExtractedFile($extractedFiles[$this->indexFile], $this->spreadsheetService->makeXslxSheet($extractedFiles[$this->indexFile], 'Electricité - C5'));
        return $extractedFiles;
    }
}