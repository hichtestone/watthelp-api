<?php

declare(strict_types=1);

namespace App\Import\Verifier\Budget;

use App\Entity\Client;
use App\Exceptions\ImportException;
use App\Import\Verifier\AbstractVerifier;
use App\Import\Verifier\ColumnVerifier;
use App\Manager\DeliveryPointManager;
use App\Model\Import\Budget\BudgetImportData;
use App\Model\Import\Budget\DeliveryPointBudgetImportData;
use App\Service\SpreadsheetService;
use Doctrine\Common\Collections\ArrayCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Contracts\Translation\TranslatorInterface;

class BudgetVerifier extends AbstractVerifier
{
    private const BUDGETS_SHEET_NAME = 'Budgets';
    private const DELIVERY_POINT_BUDGETS_SHEET_NAME = 'Budgets PDL';
    private const SHEET_NAMES = [self::BUDGETS_SHEET_NAME, self::DELIVERY_POINT_BUDGETS_SHEET_NAME];
    private DeliveryPointManager $deliveryPointManager;
    private int $firstRowIndex = 2;
    private SpreadsheetService $spreadsheetService;
    private ArrayCollection $budgetsColumns;
    private ArrayCollection $dpBudgetsColumns;
    private Client $client;

    public function __construct(
        DeliveryPointManager $deliveryPointManager,
        SpreadsheetService $spreadsheetService,
        TranslatorInterface $translator
    ) {
        $this->deliveryPointManager = $deliveryPointManager;
        $this->spreadsheetService = $spreadsheetService;
        $this->translator = $translator;

        $this->budgetsColumns = new ArrayCollection([
            new ColumnVerifier('A1', 'Année'),
            new ColumnVerifier('B1', 'Nombre d\'heures de fonctionnement annuel'),
            new ColumnVerifier('C1', 'Prix moyen énergie HT (€/kWh)'),
            new ColumnVerifier('D1', 'Consommation annuelle (kWh)'),
            new ColumnVerifier('E1', 'Montant annuel (€)')
        ]);

        $this->dpBudgetsColumns = new ArrayCollection([
            new ColumnVerifier('A1', 'Année'),
            new ColumnVerifier('B1', 'Référence PDL'),
            new ColumnVerifier('C1', 'Budget prévisionnel annuel HT (€)'),
            new ColumnVerifier('D1', 'Budget consommation prévisionnel annuel (kWh)'),
            new ColumnVerifier('E1', 'Puissance installée (kWh)'),
            new ColumnVerifier('F1', 'Consommation appareillage (%)'),
            new ColumnVerifier('G1', 'Gradation (%)'),
            new ColumnVerifier('H1', 'Nombre d\'heures de gradation'),
            new ColumnVerifier('I1', 'Consommation prévisionnelle annuelle (kWh)'),
            new ColumnVerifier('J1', 'Réalisation de travaux (oui/non)'),
            new ColumnVerifier('K1', 'Date de réalisation des travaux'),
            new ColumnVerifier('L1', 'Puissance installée après travaux (kWh)'),
            new ColumnVerifier('M1', 'Consommation appareillage après travaux (kWh)'),
            new ColumnVerifier('N1', 'Gradation après travaux (kWh)'),
            new ColumnVerifier('O1', 'Nombre d\'heures de gradation après travaux'),
            new ColumnVerifier('P1', 'Consommation annuelle après travaux (kWh)')
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

        [
            self::BUDGETS_SHEET_NAME => $budgetsSheet,
            self::DELIVERY_POINT_BUDGETS_SHEET_NAME => $dpBudgetsSheet
        ] = $this->spreadsheetService->makeXlsxSheets($filePath, self::SHEET_NAMES);

        $this->verifySheetNames($budgetsSheet, $dpBudgetsSheet);
        $this->verifyColumns($budgetsSheet, $dpBudgetsSheet);
        $budgets = $this->verifyData($budgetsSheet, $dpBudgetsSheet);

        return $budgets;
    }

    private function verifySheetNames(?Worksheet $budgetsSheet, ?Worksheet $dpBudgetsSheet): void
    {
        $errors = [];
        if (!$budgetsSheet) {
            $errors[] = 'Le fichier est incorrect: l\'onglet ' . self::BUDGETS_SHEET_NAME . ' n\'existe pas.';
        }
        if (!$dpBudgetsSheet) {
            $errors[] = 'Le fichier est incorrect: l\'onglet ' . self::DELIVERY_POINT_BUDGETS_SHEET_NAME . ' n\'existe pas.';
        }
        if (!empty($errors)) {
            throw new ImportException($errors);
        }
    }

    private function verifyColumns(Worksheet $budgetsSheet, Worksheet $dpBudgetsSheet): void
    {
        $errors = $this->verifyFileColumns($budgetsSheet, self::BUDGETS_SHEET_NAME, $this->budgetsColumns);
        $errors = array_merge($errors, $this->verifyFileColumns($dpBudgetsSheet, self::DELIVERY_POINT_BUDGETS_SHEET_NAME, $this->dpBudgetsColumns));

        if (!empty($errors)) {
            throw new ImportException($errors);
        }
    }

    private function verifyData(Worksheet $budgetsSheet, Worksheet $dpBudgetsSheet): array
    {
        $budgets = $this->verifyBudgetsSheetData($budgetsSheet);
        $budgets = $this->verifyDpBudgetsSheetData($dpBudgetsSheet, $budgets);

        return $budgets;
    }

    private function verifyBudgetsSheetData(Worksheet $sheet): array
    {
        $budgets = $years = $errors = [];
        $lastRow = $sheet->getHighestDataRow();
        foreach ($sheet->getRowIterator($this->firstRowIndex) as $row) {
            if ($row->getRowIndex() > $lastRow) {
                break;
            }
            $rowIndex = strval($row->getRowIndex());

            $budgetImportData = new BudgetImportData();

            $year = $this->dataToInt($this->getCalculatedCellValue($sheet, 'A', $rowIndex));
            if (!$year) {
                $errors[] = "L'onglet Budgets est incorrect: Cellule A$rowIndex, l'Année est obligatoire.";
            } else if (in_array($year, $years)) {
                $errors[] = "L'onglet Budgets est incorrect: Cellule A$rowIndex, l'Année doit être unique.";
            } else {
                $budgetImportData->year = $year;
                $years[] = $budgetImportData->year;
            }

            $totalHours = $this->dataToInt($this->getCalculatedCellValue($sheet, 'B', $rowIndex));
            if (is_null($totalHours)) {
                $errors[] = "L'onglet Budgets est incorrect: Cellule B$rowIndex, le Nombre d'heures de fonctionnement annuel est obligatoire.";
            } else {
                $budgetImportData->totalHours = $totalHours;
            }

            $averagePrice = $this->amountToInt($this->getCalculatedCellValue($sheet, 'C', $rowIndex));
            if (is_null($averagePrice)) {
                $errors[] = "L'onglet Budgets est incorrect: Cellule C$rowIndex, le Prix moyen énergie HT est obligatoire.";
            } else {
                $budgetImportData->averagePrice = $averagePrice;
            }

            $budgetImportData->totalConsumption = $this->floatToInt($this->getCalculatedCellValue($sheet, 'D', $rowIndex));
            $budgetImportData->totalAmount = $this->amountToInt($this->getCalculatedCellValue($sheet, 'E', $rowIndex));

            if ($year) {
                $budgets[$year] = $budgetImportData;
            }
        }

        if (!empty($errors)) {
            throw new ImportException($errors);
        }

        return $budgets;
    }

    private function verifyDpBudgetsSheetData(Worksheet $sheet, array $budgets): array
    {
        $deliveryPointReferences = $errors = [];
        $years = array_keys($budgets);
        $lastRow = $sheet->getHighestDataRow();
        foreach ($sheet->getRowIterator($this->firstRowIndex) as $row) {
            if ($row->getRowIndex() > $lastRow) {
                break;
            }
            $rowIndex = strval($row->getRowIndex());

            $dpImportData = new DeliveryPointBudgetImportData();

            $dpImportData->year = $this->dataToInt($this->getCalculatedCellValue($sheet, 'A', $rowIndex)) ?? 0;
            $dpImportData->dpRef = $this->dataToString($this->getCalculatedCellValue($sheet, 'B', $rowIndex)) ?? '';
            $dpImportData->total = $this->amountToInt($this->getCalculatedCellValue($sheet, 'C', $rowIndex));
            $dpImportData->totalConsumption = $this->floatToInt($this->getCalculatedCellValue($sheet, 'D', $rowIndex));
            $dpImportData->installedPower = $this->dataToString($this->getCalculatedCellValue($sheet, 'E', $rowIndex));
            $dpImportData->equipmentPowerPercentage = $this->floatToInt($this->getCalculatedCellValue($sheet, 'F', $rowIndex));
            $dpImportData->gradation = $this->floatToInt($this->getCalculatedCellValue($sheet, 'G', $rowIndex));
            $dpImportData->gradationHours = $this->dataToInt($this->getCalculatedCellValue($sheet, 'H', $rowIndex));
            $dpImportData->subTotalConsumption = $this->floatToInt($this->getCalculatedCellValue($sheet, 'I', $rowIndex));
            $dpImportData->renovationRaw = strtolower($this->dataToString($this->getCalculatedCellValue($sheet, 'J', $rowIndex)) ?? '');
            $dpImportData->renovation = $dpImportData->renovationRaw === 'oui';
            $dpImportData->renovatedAt = $this->dataToDateTime($this->getCalculatedCellValue($sheet, 'K', $rowIndex));
            $dpImportData->newInstalledPower = $this->dataToString($this->getCalculatedCellValue($sheet, 'L', $rowIndex));
            $dpImportData->newEquipmentPowerPercentage = $this->floatToInt($this->getCalculatedCellValue($sheet, 'M', $rowIndex));
            $dpImportData->newGradation = $this->floatToInt($this->getCalculatedCellValue($sheet, 'N', $rowIndex));
            $dpImportData->newGradationHours = $this->dataToInt($this->getCalculatedCellValue($sheet, 'O', $rowIndex));
            $dpImportData->newSubTotalConsumption = $this->floatToInt($this->getCalculatedCellValue($sheet, 'P', $rowIndex));

            $rowErrors = $this->verifyDpBudgetsSheetRowData($dpImportData, $rowIndex, $years, $deliveryPointReferences);

            if (empty($rowErrors)) {
                $budgets[$dpImportData->year]->dpBudgets[] = $dpImportData;
            } else {
                array_push($errors, ...$rowErrors);
            }
        }

        $dpRefsInImportFile = [];
        foreach ($deliveryPointReferences as $year => $dpRefs) {
            $dpRefsInImportFile = array_merge($dpRefsInImportFile, $dpRefs);
        }
        $dpRefsInImportFile = array_unique($dpRefsInImportFile);
        $existingDeliveryPoints = $this->deliveryPointManager->findByFilters($this->client, ['references' => $dpRefsInImportFile]);

        if (count($existingDeliveryPoints) !== count($dpRefsInImportFile)) {
            $existingReferences = [];
            foreach ($existingDeliveryPoints as $existingDeliveryPoint) {
                $existingReferences[] = $existingDeliveryPoint->getReference();
            }
            $nonexistentReferences = array_diff($dpRefsInImportFile, $existingReferences);
            $errors[] = 'Le ou les point(s) de livraison ' . implode(', ', $nonexistentReferences) . ' n\'existent pas.';
        }

        if (!empty($errors)) {
            throw new ImportException($errors);
        }

        return $budgets;
    }

    private function verifyDpBudgetsSheetRowData(DeliveryPointBudgetImportData $importData, string $rowIndex, array $years, array &$deliveryPointReferences): array
    {
        $errors = [];

        if (!$importData->year) {
            $errors[] = "L'onglet Budgets PDL est incorrect: Cellule A$rowIndex, l'Année est obligatoire.";
        } else if (!in_array($importData->year, $years)) {
            $errors[] = "L'onglet Budgets PDL est incorrect: Cellule A$rowIndex, l'Année {$importData->year} n'existe pas dans l'onglet Budgets.";
        }

        if (!$importData->dpRef) {
            $errors[] = "L'onglet Budgets PDL est incorrect: Cellule B$rowIndex, la Référence PDL est obligatoire.";
        } else if ($importData->year) {
            $deliveryPointReferences[$importData->year] ??= [];
            if (in_array($importData->dpRef, $deliveryPointReferences[$importData->year])) {
                $errors[] = "Le fichier est incorrect: Cellule B$rowIndex, la Référence {$importData->dpRef} pour l'année {$importData->year} n'est pas unique.";
            } else {
                $deliveryPointReferences[$importData->year][] = $importData->dpRef;    
            }
        }

        if ($importData->renovationRaw !== 'oui' && $importData->renovationRaw !== 'non') {
            $errors[] = $this->createError("J$rowIndex", $importData->renovationRaw, '"oui" ou "non"');
        }

        if (!is_null($importData->renovatedAt) && $importData->year) {
            $renovatedAtYear = intval($importData->renovatedAt->format('Y'));
            if ($renovatedAtYear !== $importData->year) {
                $errors[] = "L'onglet Budgets PDL est incorrect: Cellule K$rowIndex, la date doit appartenir à l'année {$importData->year} spécifiée dans la cellule A$rowIndex.";
            }
        }

        return $errors;
    }
}