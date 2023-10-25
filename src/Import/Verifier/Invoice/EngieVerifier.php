<?php

declare(strict_types=1);

namespace App\Import\Verifier\Invoice;

use App\Entity\Client;
use App\Exceptions\ImportException;
use App\Exceptions\InvalidFileException;
use App\Import\Invoice\EngieTrait;
use App\Import\Verifier\ColumnVerifier;
use App\Manager\InvoiceManager;
use App\Service\SpreadsheetService;
use App\Service\ZipService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Contracts\Translation\TranslatorInterface;

class EngieVerifier extends AbstractInvoiceVerifier implements VerifierInterface
{
    use EngieTrait;

    private Collection $invoiceFileColumns;
    private Collection $indexFileColumns;

    public function __construct(
        ZipService $zipService,
        InvoiceManager $invoiceManager,
        SpreadsheetService $spreadsheetService,
        TranslatorInterface $translator
    ) {
        $this->zipService = $zipService;
        $this->invoiceManager = $invoiceManager;
        $this->spreadsheetService = $spreadsheetService;
        $this->translator = $translator;
        $this->requiredFiles = [$this->invoiceFile, $this->indexFile];

        $this->invoiceFileColumns = new ArrayCollection([
            new ColumnVerifier('A13', 'N° Document'),
            new ColumnVerifier('B13', 'Type de document'),
            new ColumnVerifier('C13', 'Facture annulée'),
            new ColumnVerifier('D13', 'N° FMC/FUM/Bordereau'),
            new ColumnVerifier('E13', 'Compte de contrat'),
            new ColumnVerifier('F13', 'Compte de contrat collectif'),
            new ColumnVerifier('G13', 'Libellé du CCC'),
            new ColumnVerifier('H13', 'Date d\'édition'),
            new ColumnVerifier('I13', 'Raison Sociale Payeur'),
            new ColumnVerifier('J13', 'Adresse1 Payeur'),
            new ColumnVerifier('K13', 'Code Postal Payeur'),
            new ColumnVerifier('L13', 'Localité Payeur'),
            new ColumnVerifier('M13', 'Ref contrat client'),
            new ColumnVerifier('N13', 'Ref compta client'),
            new ColumnVerifier('O13', 'N° de marché'),
            new ColumnVerifier('P13', 'Code Paquet'),
            new ColumnVerifier('Q13', 'Libellé Paquet'),
            new ColumnVerifier('R13', 'Nom du site'),
            new ColumnVerifier('S13', 'Adresse Site'),
            new ColumnVerifier('T13', 'Code Postal Site'),
            new ColumnVerifier('U13', 'Localité Site'),
            new ColumnVerifier('V13', 'PDL'),
            new ColumnVerifier('W13', 'Installation'),
            new ColumnVerifier('X13', 'fréquence de relève'),
            new ColumnVerifier('Y13', 'Date de début de période de consommation'),
            new ColumnVerifier('Z13', 'Date de fin de période de consommation'),
            new ColumnVerifier('AA13', 'Nombre de jours entre le début et la fin de la période facturée'),
            new ColumnVerifier('AB12', 'Libellé offre'),
            new ColumnVerifier('AC13', 'Consommation totale (kWh)'),
            new ColumnVerifier('AD13', 'Consommation BASE (kWh)'),
            new ColumnVerifier('AE13', 'Consommation HP (kWh)'),
            new ColumnVerifier('AF13', 'Consommation HC (kWh)'),
            new ColumnVerifier('AG12', 'Puissance souscrite'),
            new ColumnVerifier('AH13', 'Part fixe'),
            new ColumnVerifier('AI13', 'Part variable'),
            new ColumnVerifier('AJ13', 'Total fourniture HTT (TURPE inclus pour les offres Energie fixe)'),
            new ColumnVerifier('AK13', 'Montant facturé base'),
            new ColumnVerifier('AL13', 'Montant facturé HP'),
            new ColumnVerifier('AM13', 'Montant facturé HC'),
            new ColumnVerifier('AN13', 'Total services'),
            new ColumnVerifier('AO13', 'Totale frais de gestion'),
            new ColumnVerifier('AP12', 'Montant total agrégé HTT'),
            new ColumnVerifier('AQ13', 'Total CSPE'),
            new ColumnVerifier('AR13', 'Total Taxes Locales Elec'),
            new ColumnVerifier('AS13', 'Total TICFE'),
            new ColumnVerifier('AT13', 'Total CTA Elec'),
            new ColumnVerifier('AU12', 'Montant HTVA'),
            new ColumnVerifier('AV13', 'Montant TVA taux réduit'),
            new ColumnVerifier('AW13', 'Montant TVA taux normal'),
            new ColumnVerifier('AX13', 'Montant total de la TVA'),
            new ColumnVerifier('AY12', 'Montant total TTC'),
            new ColumnVerifier('AZ13', 'premier index relève Base'),
            new ColumnVerifier('BA13', 'dernier index relève Base'),
            new ColumnVerifier('BB13', 'premier index relève HP'),
            new ColumnVerifier('BC13', 'dernier index relève HP'),
            new ColumnVerifier('BD13', 'premier index relève HC'),
            new ColumnVerifier('BE13', 'dernier index relève HC')
        ]);

        $this->indexFileColumns = new ArrayCollection([
            new ColumnVerifier('A12', 'Nom du site'),
            new ColumnVerifier('B12', 'PRM'),
            new ColumnVerifier('C12', 'Date index'),
            new ColumnVerifier('D12', 'Index simple (kWh)'),
            new ColumnVerifier('E12', 'Index HP (kWh)'),
            new ColumnVerifier('F12', 'Index  HC (kWh)'),
            new ColumnVerifier('G12', 'Puissance Souscrite (kVa)'),
            new ColumnVerifier('H12', 'Numéro de facture')
        ]);
    }

    /**
     * @throws Exception
     * @throws InvalidFileException
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function verify(string $zipPath, array $invoiceReferencesToReimport, Client $client): void
    {
        $extractedFiles = $this->extract($zipPath);
        try {
            $this->verifyColumns($extractedFiles);
            if (empty($invoiceReferencesToReimport)) {
                $this->verifyInvoicesHaveNotAlreadyBeenImported($extractedFiles, $client);
            }
            $this->verifyInvoiceFileData($extractedFiles[$this->invoiceFile]->getSheet(), $invoiceReferencesToReimport, $client);
        } finally {
            foreach ($extractedFiles as $extractedFile) {
                unlink($extractedFile->getPath());
            }
        }
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function verifyColumns(array $extractedFiles): void
    {
        $errors = $this->verifyFileColumns($extractedFiles[$this->invoiceFile]->getSheet(), $this->invoiceFile, $this->invoiceFileColumns);
        $errors = array_merge($errors, $this->verifyFileColumns($extractedFiles[$this->indexFile]->getSheet(), $this->indexFile, $this->indexFileColumns));

        if (!empty($errors)) {
            throw new ImportException($errors);
        }
    }

    private function verifyInvoiceFileData(Worksheet $sheet, array $invoiceReferencesToReimport, Client $client): void
    {
        $errors = [];
        $lastRow = $sheet->getHighestDataRow();
        $checkInvoiceRef = !empty($invoiceReferencesToReimport);
        foreach ($sheet->getRowIterator($this->firstRowIndex) as $row) {
            if ($row->getRowIndex() > $lastRow) {
                break;
            }
            $rowIndex = strval($row->getRowIndex());
            if (is_null($this->getCellValue($sheet, 'A', $rowIndex))) {
                continue;
            }

            // skip row if we are reimporting invoices and the current row isn't one of them
            if ($checkInvoiceRef && !in_array($this->dataToString($this->getCellValue($sheet, 'D', $rowIndex)), $invoiceReferencesToReimport)) {
                continue;
            }

            $totalConsumption = $this->dataToInt($this->getCellValue($sheet, 'AC', $rowIndex));
            $baseConsumption = $this->dataToInt($this->getCellValue($sheet, 'AD', $rowIndex));
            $consumptionHP = $this->dataToInt($this->getCellValue($sheet, 'AE', $rowIndex));
            $consumptionHC = $this->dataToInt($this->getCellValue($sheet, 'AF', $rowIndex));
            $powerSubscribed = $this->dataToString($this->getCellValue($sheet, 'AG', $rowIndex));
            $powerSubscribedValue = floatval(str_replace(',', '.', $powerSubscribed));
            $totalServicesRawValue = $this->getCellValue($sheet, 'AN', $rowIndex);
            $totalServices = $this->amountToInt($totalServicesRawValue);
            $totalManagementCostRawValue = $this->getCellValue($sheet, 'AO', $rowIndex);
            $totalManagementCost = $this->amountToInt($totalManagementCostRawValue);
            $totalExcludedRawValue = $this->getCellValue($sheet, 'AJ', $rowIndex);
            $totalExcluded = $this->amountToInt($totalExcludedRawValue);
            $amountInvoicedRawValue = $this->getCellValue($sheet, 'AK', $rowIndex);
            $amountInvoiced = $this->amountToInt($amountInvoicedRawValue);
            $zipCode = $this->dataToString($this->getCellValue($sheet, 'T', $rowIndex)) ?? '';
            $city = $this->dataToString($this->getCellValue($sheet, 'U', $rowIndex)) ?? '';

            if ($totalConsumption !== $baseConsumption) {
                $errors[] = $this->createError("AC$rowIndex", strval($totalConsumption), strval($baseConsumption), $this->invoiceFile);  
            }
            if ($consumptionHP) {
                $errors[] = $this->createError("AE$rowIndex", strval($consumptionHP), $this->translator->trans('zero_or_empty'), $this->invoiceFile);
            }
            if ($consumptionHC) {
                $errors[] = $this->createError("AF$rowIndex", strval($consumptionHC), $this->translator->trans('zero_or_empty'), $this->invoiceFile);
            }
            if ($powerSubscribedValue < 0.1 || $powerSubscribedValue > 36) {
                $errors[] = $this->createError("AG$rowIndex", strval($powerSubscribed), $this->translator->trans('between_0_and_36'), $this->invoiceFile);
            }
            if ($totalServices) {
                $errors[] = $this->createError("AN$rowIndex", strval($totalServicesRawValue), $this->translator->trans('zero_or_empty'), $this->invoiceFile);
            }
            if ($totalManagementCost) {
                $errors[] = $this->createError("AO$rowIndex", strval($totalManagementCostRawValue), $this->translator->trans('zero_or_empty'), $this->invoiceFile);
            }
            if ($totalExcluded !== $amountInvoiced) {
                $errors[] = $this->createError("AJ$rowIndex", strval($totalExcludedRawValue), strval($amountInvoicedRawValue), $this->invoiceFile);
            }
            if ($zipCode !== $client->getZipCode() && strtoupper($city) !== strtoupper($client->getCity() ?? '')) {
                $errors[] = $this->translator->trans('incorrect_file_wrong_city', [
                    'filename' => " {$this->invoiceFile}",
                    'row' => $rowIndex,
                    'city' => $client->getCity(),
                    'zipcode' => $client->getZipCode()
                ]);
            }
        }
        
        if (!empty($errors)) {
            throw new ImportException($errors);
        }
    }

    /**
     * @throws Exception
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Calculation\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function verifyInvoicesHaveNotAlreadyBeenImported(array $extractedFiles, Client $client): void
    {
        $this->verifyFileInvoicesHaveNotAlreadyBeenImported($extractedFiles[$this->invoiceFile]->getSheet(), $this->invoiceFile, 'D', 14, $client);
    }
}