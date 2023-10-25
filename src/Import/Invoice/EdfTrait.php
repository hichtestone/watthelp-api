<?php

declare(strict_types=1);

namespace App\Import\Invoice;

use App\Entity\Contract;
use App\Exceptions\InvalidFileException;
use App\Import\ExtractedFile;
use App\Service\SpreadsheetService;
use App\Service\ZipService;

trait EdfTrait
{
    private int $firstRowIndex = 2;
    private string $provider = Contract::PROVIDER_EDF;
    private string $sitesElecFile = 'sites_elec.csv';
    private string $sitesElecPhsFile = 'sites_elec_phs.csv';
    private string $contractualInformationFile = 'informations_contractuelles.csv';
    private array $requiredFiles = [];
    private ZipService $zipService;
    private SpreadsheetService $spreadsheetService;

    public function supports(?string $provider): bool
    {
        return $this->provider === $provider;
    }

    /**
     * @throws InvalidFileException
     */
    public function extract(string $zipPath): array
    {
        $extractedFiles = $this->zipService->extract($zipPath, $this->requiredFiles);
        foreach ($extractedFiles as &$extractedFile) {
            $extractedFile = new ExtractedFile($extractedFile, $this->spreadsheetService->makeCsvSheet($extractedFile));
        }
        return $extractedFiles;
    }
}
