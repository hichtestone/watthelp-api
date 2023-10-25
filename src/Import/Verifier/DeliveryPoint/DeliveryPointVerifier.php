<?php

declare(strict_types=1);

namespace App\Import\Verifier\DeliveryPoint;

use App\Exceptions\ImportException;
use App\Import\Verifier\AbstractVerifier;
use App\Import\Verifier\ColumnVerifier;
use App\Manager\DeliveryPointManager;
use App\Model\Import\DeliveryPoint\DeliveryPointImportData;
use App\Service\SpreadsheetService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeliveryPointVerifier extends AbstractVerifier
{
    private DeliveryPointManager $deliveryPointManager;
    private string $fileName = 'import_perimetre.xlsx';
    private int $firstRowIndex = 2;
    private SpreadsheetService $spreadsheetService;
    private Collection $fileColumns;

    public function __construct(
        DeliveryPointManager $deliveryPointManager,
        SpreadsheetService $spreadsheetService,
        TranslatorInterface $translator
    ) {
        $this->deliveryPointManager = $deliveryPointManager;
        $this->spreadsheetService = $spreadsheetService;
        $this->translator = $translator;

        $this->fileColumns = new ArrayCollection([
            new ColumnVerifier('A1', 'Nom'),
            new ColumnVerifier('B1', 'Référence'),
            new ColumnVerifier('C1', 'Code'),
            new ColumnVerifier('D1', 'Adresse'),
            new ColumnVerifier('E1', 'Latitude'),
            new ColumnVerifier('F1', 'Longitude'),
            new ColumnVerifier('G1', 'Référence compteur'),
            new ColumnVerifier('H1', 'Puissance'),
            new ColumnVerifier('I1', 'Description'),
            new ColumnVerifier('J1', 'Dans périmètre'),
            new ColumnVerifier('K1', 'Date périmètre')
        ]);
    }

    /**
     * @throws ImportException
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function verify(string $filePath): array
    {
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
        $errors = [];
        $references = [];
        $codes = [];
        $deliveryPointImportDatas = [];
        $lastRow = $sheet->getHighestDataRow();
        foreach ($sheet->getRowIterator($this->firstRowIndex) as $row) {
            if ($row->getRowIndex() > $lastRow) {
                break;
            }
            $rowIndex = strval($row->getRowIndex());

            $importData = new DeliveryPointImportData();
            $importData->name = $this->dataToString($this->getCalculatedCellValue($sheet, 'A', $rowIndex)) ?? '';
            $importData->reference = $this->dataToString($this->getCalculatedCellValue($sheet, 'B', $rowIndex)) ?? '';
            $importData->code = $this->dataToString($this->getCalculatedCellValue($sheet, 'C', $rowIndex));
            $importData->address = $this->dataToString($this->getCalculatedCellValue($sheet, 'D', $rowIndex)) ?? '';
            $importData->latitude = $this->dataToString($this->getCalculatedCellValue($sheet, 'E', $rowIndex));
            $importData->longitude = $this->dataToString($this->getCalculatedCellValue($sheet, 'F', $rowIndex));
            $importData->meterReference = $this->dataToString($this->getCalculatedCellValue($sheet, 'G', $rowIndex)) ?? '';
            $importData->power = $this->dataToString($this->getCalculatedCellValue($sheet, 'H', $rowIndex)) ?? '';
            $importData->description = $this->dataToString($this->getCalculatedCellValue($sheet, 'I', $rowIndex));
            $importData->isInScopeRaw = strtolower($this->dataToString($this->getCalculatedCellValue($sheet, 'J', $rowIndex)) ?? '');
            $importData->isInScope = $importData->isInScopeRaw === 'oui';
            $importData->scopeDate = $this->dataToDatetime($this->getCalculatedCellValue($sheet, 'K', $rowIndex)) ?? new \DateTime();

            $rowErrors = $this->verifyRowData($importData, $rowIndex, $references, $codes);
            if (empty($rowErrors)) {
                $deliveryPointImportDatas[$importData->reference] = $importData;
            } else {
                array_push($errors, ...$rowErrors);
            }
        }
        
        if (!empty($errors)) {
            throw new ImportException($errors);
        }

        return $deliveryPointImportDatas;
    }

    private function verifyRowData(DeliveryPointImportData $importData, string $rowIndex, array &$references, array &$codes): array
    {
        $errors = [];
        if (!$importData->name) {
            $errors[] = $this->translator->trans('incorrect_file_attribute_mandatory', [
                'cell' => "A$rowIndex",
                'attribute' => 'name'
            ]);
        }

        if (!$importData->reference) {
            $errors[] = $this->translator->trans('incorrect_file_attribute_mandatory', [
                'cell' => "B$rowIndex",
                'attribute' => 'reference'
            ]);
        } else if (in_array($importData->reference, $references)) {
            $errors[] = $this->translator->trans('incorrect_file_attribute_must_be_unique', [
                'cell' => "B$rowIndex",
                'attribute' => 'reference'
            ]);
        } else {
            $references[] = $importData->reference;
        }

        if ($importData->code) {
            if (in_array($importData->code, $codes)) {
                $errors[] = $this->translator->trans('incorrect_file_attribute_must_be_unique', [
                    'cell' => "C$rowIndex",
                    'attribute' => 'code'
                ]);
            } else {
                $codes[] = $importData->code;
            }
        }

        if (!$importData->address) {
            $errors[] = $this->translator->trans('incorrect_file_attribute_mandatory', [
                'cell' => "D$rowIndex",
                'attribute' => 'address'
            ]);
        }
        if (!$importData->meterReference) {
            $errors[] = $this->translator->trans('incorrect_file_attribute_mandatory', [
                'cell' => "G$rowIndex",
                'attribute' => 'meter_reference'
            ]);
        }

        $powerValue = floatval(str_replace(',', '.', $importData->power));
        if ($powerValue < 0.1 || $powerValue > 36) {
            $errors[] = $this->createError("H$rowIndex", strval($importData->power), $this->translator->trans('between_0_and_36'));
        }
        if ($importData->isInScopeRaw !== 'oui' && $importData->isInScopeRaw !== 'non') {
            $errors[] = $this->createError("J$rowIndex", $importData->isInScopeRaw, '"oui" ou "non"');
        }

        return $errors;
    }
}