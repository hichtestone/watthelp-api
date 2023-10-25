<?php

declare(strict_types=1);

namespace App\Import\Verifier\Invoice;

use App\Entity\Client;
use App\Exceptions\ImportException;
use App\Exceptions\InvalidFileException;
use App\Import\Invoice\EdfTrait;
use App\Import\Verifier\ColumnVerifier;
use App\Manager\InvoiceManager;
use App\Service\SpreadsheetService;
use App\Service\ZipService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Contracts\Translation\TranslatorInterface;

class EdfVerifier extends AbstractInvoiceVerifier implements VerifierInterface
{
    use EdfTrait;

    private Collection $sitesElecColumns;
    private Collection $sitesElecPhsColumns;
    private Collection $contractualInformationColumns;

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
        $this->requiredFiles = [$this->sitesElecFile, $this->sitesElecPhsFile, $this->contractualInformationFile];

        $this->sitesElecColumns = new ArrayCollection([
            new ColumnVerifier('A1', 'Date de la facture'),
            new ColumnVerifier('B1', 'Numéro de facture'),
            new ColumnVerifier('C1', 'Référence EDF'),
            new ColumnVerifier('D1', 'Référence d\'acheminement'),
            new ColumnVerifier('E1', 'Nom du site'),
            new ColumnVerifier('F1', 'Segmentation OR'),
            new ColumnVerifier('G1', 'Code site'),
            new ColumnVerifier('H1', 'Rue'),
            new ColumnVerifier('I1', 'Code postal'),
            new ColumnVerifier('J1', 'Ville'),
            new ColumnVerifier('K1', 'Imputation budgétaire'),
            new ColumnVerifier('L1', 'Type de facture'),
            new ColumnVerifier('M1', 'Début période de consommation'),
            new ColumnVerifier('N1', 'Fin période de consommation'),
            new ColumnVerifier('O1', 'Abonnement (Euros HT)'),
            new ColumnVerifier('P1', 'Total dépassements (Euros HT)'),
            new ColumnVerifier('Q1', 'Consommation énergie renouvelable'),
            new ColumnVerifier('R1', 'Total consommation (kWh)'),
            new ColumnVerifier('S1', 'Prix moyen HT (cEuros/kWh)'),
            new ColumnVerifier('T1', 'Option énergie renouvelable (Euros HT)'),
            new ColumnVerifier('U1', 'Mécanisme de capacité (Euros HT)'),
            new ColumnVerifier('V1', 'Total énergie facturée (Euros HT)'),
            new ColumnVerifier('W1', 'Energie réactive (Euros HT)'),
            new ColumnVerifier('X1', 'Total URD (Euros HT)'),
            new ColumnVerifier('Y1', 'Charge occasionnelle (Euros HT)'),
            new ColumnVerifier('Z1', 'Taxes locales'),
            new ColumnVerifier('AA1', 'CSPE'),
            new ColumnVerifier('AB1', 'CT'),
            new ColumnVerifier('AC1', 'Total hors TVA (Euros)'),
            new ColumnVerifier('AD1', 'Montant TVA à taux réduit (Euros)'),
            new ColumnVerifier('AE1', 'Montant TVA à taux normal (Euros)'),
            new ColumnVerifier('AF1', 'Montant total TVA (Euros)'),
            new ColumnVerifier('AG1', 'Total TTC (Euros)')
        ]);

        $this->sitesElecPhsColumns = new ArrayCollection([
            new ColumnVerifier('A1', 'Date de la facture'),
            new ColumnVerifier('B1', 'Numéro de facture'),
            new ColumnVerifier('C1', 'Référence EDF'),
            new ColumnVerifier('D1', 'Référence d\'acheminement'),
            new ColumnVerifier('E1', 'Nom du site'),
            new ColumnVerifier('F1', 'Segmentation OR'),
            new ColumnVerifier('G1', 'Code site'),
            new ColumnVerifier('H1', 'Rue'),
            new ColumnVerifier('I1', 'Code postal'),
            new ColumnVerifier('J1', 'Ville'),
            new ColumnVerifier('K1', 'Imputation budgétaire'),
            new ColumnVerifier('L1', 'Type de facture'),
            new ColumnVerifier('M1', 'Poste Horosaisonnier'),
            new ColumnVerifier('N1', 'Libellé Poste Horosaisonnier'),
            new ColumnVerifier('O1', 'Puissances souscrites (kW/kVA)'),
            new ColumnVerifier('P1', 'Puissances max atteintes (kW/kVA ou heure pour PAH/PAB)'),
            new ColumnVerifier('Q1', 'Consommation (kWh)'),
            new ColumnVerifier('R1', 'Energie facturée (Euros HT)'),
            new ColumnVerifier('S1', 'Index début de période'),
            new ColumnVerifier('T1', 'Date index début de période'),
            new ColumnVerifier('U1', 'Index fin de période'),
            new ColumnVerifier('V1', 'Date index fin de période'),
            new ColumnVerifier('W1', 'Type index fin de période')
        ]);

        $this->contractualInformationColumns = new ArrayCollection([
            new ColumnVerifier('A1', 'Référence EDF'),
            new ColumnVerifier('B1', 'Nom du site'),
            new ColumnVerifier('C1', 'Segmentation OR'),
            new ColumnVerifier('D1', 'Référence d\'acheminement'),
            new ColumnVerifier('E1', 'Code site'),
            new ColumnVerifier('F1', 'N° compteur'),
            new ColumnVerifier('G1', 'Référence PCE'),
            new ColumnVerifier('H1', 'Type d\'offre'),
            new ColumnVerifier('I1', 'Imputation budgétaire'),
            new ColumnVerifier('J1', 'Imputation site'),
            new ColumnVerifier('K1', 'Tarif réglementé, non réglementé ou individualisé'),
            new ColumnVerifier('L1', 'Référence contrat'),
            new ColumnVerifier('M1', 'Date de résiliation'),
            new ColumnVerifier('N1', 'Compte commercial'),
            new ColumnVerifier('O1', 'Rue'),
            new ColumnVerifier('P1', 'Code postal'),
            new ColumnVerifier('Q1', 'Ville'),
            new ColumnVerifier('R1', 'Groupe prédéfini IBAN'),
            new ColumnVerifier('S1', 'Groupe prédéfini Hierarchie Commerciale')
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
            $this->verifyData($extractedFiles, $invoiceReferencesToReimport, $client);
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
        $errors = $this->verifyFileColumns($extractedFiles[$this->sitesElecFile]->getSheet(), $this->sitesElecFile, $this->sitesElecColumns);
        $errors = array_merge($errors, $this->verifyFileColumns($extractedFiles[$this->sitesElecPhsFile]->getSheet(), $this->sitesElecPhsFile, $this->sitesElecPhsColumns));
        $errors = array_merge($errors, $this->verifyFileColumns($extractedFiles[$this->contractualInformationFile]->getSheet(), $this->contractualInformationFile, $this->contractualInformationColumns));

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
        $this->verifyFileInvoicesHaveNotAlreadyBeenImported($extractedFiles[$this->sitesElecFile]->getSheet(), $this->sitesElecFile, 'B', 2, $client);
        $this->verifyFileInvoicesHaveNotAlreadyBeenImported($extractedFiles[$this->sitesElecPhsFile]->getSheet(), $this->sitesElecPhsFile, 'B', 2, $client);
    }

    /**
     * @throws Exception
     * @throws ImportException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function verifyData(array $extractedFiles, array $invoiceReferencesToReimport, Client $client): void
    {
        $sitesElecErrors = $this->verifySitesElecData($extractedFiles[$this->sitesElecFile]->getSheet(), $invoiceReferencesToReimport, $client);
        $sitesElecPhsErrors = $this->verifySitesElecPhsData($extractedFiles[$this->sitesElecPhsFile]->getSheet(), $invoiceReferencesToReimport, $client);
        $errors = array_merge($sitesElecErrors, $sitesElecPhsErrors);
        if (!empty($errors)) {
            throw new ImportException($errors);
        }
    }

    /**
     * @throws Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function verifySitesElecPhsData(Worksheet $sheet, array $invoiceReferencesToReimport, Client $client): array
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
            if ($checkInvoiceRef && !in_array($this->dataToString($this->getCellValue($sheet, 'B', $rowIndex)), $invoiceReferencesToReimport)) {
                continue;
            }

            $segment = $this->dataToString($this->getCellValue($sheet, 'F', $rowIndex));
            $budgetaryImputation = $this->dataToString($this->getCellValue($sheet, 'K', $rowIndex)) ?? '';
            $timeSeasonalityStation = $this->dataToString($this->getCellValue($sheet, 'M', $rowIndex));
            $timeSeasonalityStationWording = $this->dataToString($this->getCellValue($sheet, 'N', $rowIndex));
            $powerSubscribed = $this->dataToString($this->getCellValue($sheet, 'O', $rowIndex));
            $powerSubscribedValue = floatval(str_replace(',', '.', $powerSubscribed));
            $zipCode = $this->dataToString($this->getCellValue($sheet, 'I', $rowIndex)) ?? '';
            $city = $this->dataToString($this->getCellValue($sheet, 'J', $rowIndex)) ?? '';

            if ($segment !== 'C5') {
                $errors[] = $this->createError("F$rowIndex", strval($segment), 'C5', $this->sitesElecPhsFile);
            }
            if (strpos($budgetaryImputation, 'F8') !== 0) {
                $errors[] = $this->createError("K$rowIndex", strval($budgetaryImputation), 'F8', $this->sitesElecPhsFile);
            }
            if ($timeSeasonalityStation !== 'UN') {
                $errors[] = $this->createError("M$rowIndex", strval($timeSeasonalityStation), 'UN', $this->sitesElecPhsFile);
            }
            if ($timeSeasonalityStationWording !== 'Période unique') {
                $errors[] = $this->createError("N$rowIndex", strval($timeSeasonalityStationWording), 'Période unique', $this->sitesElecPhsFile);
            }
            if ($powerSubscribedValue < 0.1 || $powerSubscribedValue > 36) {
                $errors[] = $this->createError("O$rowIndex", strval($powerSubscribed), $this->translator->trans('between_0_and_36'), $this->sitesElecPhsFile);
            }
            if ($zipCode !== $client->getZipCode() && strtoupper($city) !== strtoupper($client->getCity() ?? '')) {
                 $errors[] = $this->translator->trans('incorrect_file_wrong_city', [
                    'filename' => " {$this->sitesElecPhsFile}",
                    'row' => $rowIndex,
                    'city' => $client->getCity(),
                    'zipcode' => $client->getZipCode()
                ]);
            }
        }
        return $errors;
    }

    /**
     * @throws Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function verifySitesElecData(Worksheet $sheet, array $invoiceReferencesToReimport, Client $client): array
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
            if ($checkInvoiceRef && !in_array($this->dataToString($this->getCellValue($sheet, 'B', $rowIndex)), $invoiceReferencesToReimport)) {
                continue;
            }

            $segment = $this->dataToString($this->getCellValue($sheet, 'F', $rowIndex));
            $budgetaryImputation = $this->dataToString($this->getCellValue($sheet, 'K', $rowIndex)) ?? '';
            $renewableEnergy = $this->dataToInt($this->getCellValue($sheet, 'Q', $rowIndex));
            $renewableEnergyOptionAmountRawValue = $this->getCellValue($sheet, 'T', $rowIndex);
            $renewableEnergyOptionAmount = $this->amountToInt($renewableEnergyOptionAmountRawValue);
            $capacityAmountRawValue = $this->getCellValue($sheet, 'U', $rowIndex);
            $capacityAmount = $this->amountToInt($capacityAmountRawValue);
            $reactiveEnergyAmountRawValue = $this->getCellValue($sheet, 'W', $rowIndex);
            $reactiveEnergyAmount = $this->amountToInt($reactiveEnergyAmountRawValue);
            $zipCode = $this->dataToString($this->getCellValue($sheet, 'I', $rowIndex)) ?? '';
            $city = $this->dataToString($this->getCellValue($sheet, 'J', $rowIndex)) ?? '';
            
            if ($segment !== 'C5') {
                $errors[] = $this->createError("F$rowIndex", strval($segment), 'C5', $this->sitesElecFile);
            }
            if (strpos($budgetaryImputation, 'F8') !== 0) {
                $errors[] = $this->createError("K$rowIndex", strval($budgetaryImputation), 'F8', $this->sitesElecFile);
            }
            if ($renewableEnergy) {
                $errors[] = $this->createError("Q$rowIndex", strval($renewableEnergy), $this->translator->trans('zero_or_empty'), $this->sitesElecFile);
            }
            if ($renewableEnergyOptionAmount) {
                $errors[] = $this->createError("T$rowIndex", strval($renewableEnergyOptionAmountRawValue), '0 ou vide', $this->sitesElecFile);
            }
            if ($capacityAmount) {
                $errors[] = $this->createError("U$rowIndex", strval($capacityAmountRawValue), $this->translator->trans('zero_or_empty'), $this->sitesElecFile);
            }
            if ($reactiveEnergyAmount) {
                $errors[] = $this->createError("W$rowIndex", strval($reactiveEnergyAmountRawValue), $this->translator->trans('zero_or_empty'), $this->sitesElecFile);
            }
            if ($zipCode !== $client->getZipCode() && strtoupper($city) !== strtoupper($client->getCity() ?? '')) {
                $errors[] = $this->translator->trans('incorrect_file_wrong_city', [
                    'filename' => " {$this->sitesElecFile}",
                    'row' => $rowIndex,
                    'city' => $client->getCity(),
                    'zipcode' => $client->getZipCode()
                ]);
            }
        }
        return $errors;
    }
}