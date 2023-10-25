<?php

declare(strict_types=1);

namespace App\Import\Verifier\Pricing;

use App\Entity\Client;
use App\Exceptions\ImportException;
use App\Import\Verifier\AbstractVerifier;
use App\Import\Verifier\ColumnVerifier;
use App\Manager\PricingManager;
use App\Model\Import\Pricing\PricingImportData;
use App\Service\SpreadsheetService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Contracts\Translation\TranslatorInterface;

class PricingVerifier extends AbstractVerifier
{
    private PricingManager $pricingManager;
    private string $fileName = 'import_tarifs.xlsx';
    private int $firstRowIndex = 2;
    private SpreadsheetService $spreadsheetService;
    private Collection $fileColumns;
    private Client $client;

    public function __construct(
        PricingManager $pricingManager,
        SpreadsheetService $spreadsheetService,
        TranslatorInterface $translator
    )
    {
        $this->pricingManager = $pricingManager;
        $this->spreadsheetService = $spreadsheetService;
        $this->translator = $translator;

        $this->fileColumns = new ArrayCollection([
            new ColumnVerifier('A1', 'Nom'),
            new ColumnVerifier('B1', 'Type'),
            new ColumnVerifier('C1', 'Date de début'),
            new ColumnVerifier('D1', 'Date de fin'),
            new ColumnVerifier('E1', 'Consommation (cts €/kWh)'),
            new ColumnVerifier('F1', 'Abonnement (€/kVA/mois)'),
        ]);
    }

    /**
     * @throws ImportException
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function verify(string $filePath, Client $client): array
    {
        $this->client = $client;
        $sheet = $this->spreadsheetService->makeXslxSheet($filePath);
        $errors = $this->verifyFileColumns($sheet, $this->fileName, $this->fileColumns);
        if (!empty($errors)) {
            throw new ImportException($errors);
        }
        return $this->verifyFileData($sheet);
    }

    /**
     * @throws ImportException
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function verifyFileData(Worksheet $sheet): array
    {
        $errors = $pricingImportData = [];
        $lastRow = $sheet->getHighestDataRow();
        foreach ($sheet->getRowIterator($this->firstRowIndex) as $row) {
            if ($row->getRowIndex() > $lastRow) {
                break;
            }
            $rowIndex = strval($row->getRowIndex());

            $importData = new PricingImportData();

            $importData->name = $this->dataToString($this->getCalculatedCellValue($sheet, 'A', $rowIndex)) ?? '';
            $importData->type = strtolower($this->dataToString($this->getCalculatedCellValue($sheet, 'B', $rowIndex))) ?? '';
            $importData->startedAt = $this->dataToDatetime($this->getCalculatedCellValue($sheet, 'C', $rowIndex)) ?? new \DateTime();
            $importData->finishedAt = $this->dataToDatetime($this->getCalculatedCellValue($sheet, 'D', $rowIndex)) ?? new \DateTime();
            $importData->consumptionBasePrice = $this->amountToInt($this->dataToString($this->getCalculatedCellValue($sheet, 'E', $rowIndex)));
            $importData->subscriptionPrice = $this->amountToInt($this->dataToString($this->getCalculatedCellValue($sheet, 'F', $rowIndex)), true);

            $rowErrors = $this->verifyRowData($importData, $rowIndex);

            if (empty($rowErrors)) {
                $pricingImportData[] = $importData;
            } else {
                array_push($errors, ...$rowErrors);
            }
        }

        if (!empty($errors)) {
            throw new ImportException($errors);
        }


        return $pricingImportData;
    }

    private function verifyRowData(PricingImportData $importData, string $rowIndex): array
    {
        $errors = [];
        if (!$importData->name) {
            $errors[] = $this->translator->trans('incorrect_file_attribute_mandatory', [
                'cell' => "A$rowIndex",
                'attribute' => 'name'
            ]);
        }

        if ($importData->type !== 'trv' && $importData->type !== 'offre de marché') {
            $errors[] = $this->createError("B$rowIndex", $importData->type, '"TRV" ou "Offre de marché"');
        }

        if ($importData->type === 'trv' && (!$importData->consumptionBasePrice && !$importData->subscriptionPrice)) {
            $errors[] = $this->translator->trans('incorrect_file_attribute_mandatory', [
                'cell' => "E$rowIndex",
                'attribute' => 'consumption'
            ]);

            $errors[] = $this->translator->trans('incorrect_file_attribute_mandatory', [
                'cell' => "F$rowIndex",
                'attribute' => 'subscription'
            ]);
        }

        if ($importData->type === 'trv' && !$importData->consumptionBasePrice) {
            $errors[] = $this->translator->trans('incorrect_file_attribute_mandatory', [
                'cell' => "E$rowIndex",
                'attribute' => 'consumption'
            ]);
        }

        if ($importData->type === 'trv' && !$importData->subscriptionPrice) {
            $errors[] = $this->translator->trans('incorrect_file_attribute_mandatory', [
                'cell' => "F$rowIndex",
                'attribute' => 'subscription'
            ]);
        }

        if ($importData->type === 'offre de marché' && !$importData->consumptionBasePrice) {
            $errors[] = $this->translator->trans('incorrect_file_attribute_mandatory', [
                'cell' => "E$rowIndex",
                'attribute' => 'consumption'
            ]);

        }

        return $errors;
    }
}
