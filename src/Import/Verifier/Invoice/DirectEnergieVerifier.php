<?php

declare(strict_types=1);

namespace App\Import\Verifier\Invoice;

use App\Entity\Client;
use App\Exceptions\ImportException;
use App\Exceptions\InvalidFileException;
use App\Import\Invoice\DirectEnergieTrait;
use App\Import\Verifier\ColumnVerifier;
use App\Manager\InvoiceManager;
use App\Service\SpreadsheetService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Contracts\Translation\TranslatorInterface;

class DirectEnergieVerifier extends AbstractInvoiceVerifier implements VerifierInterface
{
    use DirectEnergieTrait;

    private Collection $invoiceFileColumns;

    public function __construct(InvoiceManager $invoiceManager, SpreadsheetService $spreadsheetService, TranslatorInterface $translator)
    {
        $this->invoiceManager = $invoiceManager;
        $this->spreadsheetService = $spreadsheetService;
        $this->translator = $translator;

        $this->invoiceFileColumns = new ArrayCollection([
            new ColumnVerifier('A1', 'NUM BP GROUPE'),
            new ColumnVerifier('B1', 'NOM BP GROUPE'),
            new ColumnVerifier('C1', 'NUM BP ENSEIGNE'),
            new ColumnVerifier('D1', 'NOM BP ENSEIGNE'),
            new ColumnVerifier('E1', 'REF REGROUPEMENT'),
            new ColumnVerifier('F1', 'LIB REGROUPEMENT'),
            new ColumnVerifier('G1', 'SIRET CHORUS'),
            new ColumnVerifier('H1', 'CODE SERVICE'),
            new ColumnVerifier('I1', 'CODE ENGAGEMENT'),
            new ColumnVerifier('J1', 'NUM FACTURE'),
            new ColumnVerifier('K1', 'DATE COMPTABLE'),
            new ColumnVerifier('L1', 'DATE D\'ECHEANCE'),
            new ColumnVerifier('M1', 'ID DETAIL'),
            new ColumnVerifier('N1', 'TYPE DETAIL'),
            new ColumnVerifier('O1', 'REF ORIGINE'),
            new ColumnVerifier('P1', 'REF SITE'),
            new ColumnVerifier('Q1', 'NOM SITE'),
            new ColumnVerifier('R1', 'CODE INTERNE'),
            new ColumnVerifier('S1', 'AUTRE REFERENCE'),
            new ColumnVerifier('T1', 'PDL/PRM'),
            new ColumnVerifier('U1', 'ADRESSE'),
            new ColumnVerifier('V1', 'CODE POSTAL'),
            new ColumnVerifier('W1', 'VILLE'),
            new ColumnVerifier('X1', 'TYPE DE COMPTEUR'),
            new ColumnVerifier('Y1', 'NUM COMPTEUR'),
            new ColumnVerifier('Z1', 'SEGMENT'),
            new ColumnVerifier('AA1', 'RACCORDEMENT'),
            new ColumnVerifier('AB1', 'OPTION TARIFAIRE'),
            new ColumnVerifier('AC1', 'TARIF'),
            new ColumnVerifier('AD1', 'PUISSANCE SOUSCRITE'),
            new ColumnVerifier('AE1', 'DEBUT ABO'),
            new ColumnVerifier('AF1', 'FIN ABO'),
            new ColumnVerifier('AG1', 'MONTANT ABO'),
            new ColumnVerifier('AH1', 'DEBUT CONSO ET ACH. VAR.'),
            new ColumnVerifier('AI1', 'FIN CONSO ET ACH. VAR.'),
            new ColumnVerifier('AJ1', 'MONTANT CONSO'),
            new ColumnVerifier('AK1', 'MONTANT GO'),
            new ColumnVerifier('AL1', 'MONTANT CEE'),
            new ColumnVerifier('AM1', 'MONTANT CEE PRECARITE'),
            new ColumnVerifier('AN1', 'MONTANT ARENH'),
            new ColumnVerifier('AO1', 'MONTANT CAPACITE'),
            new ColumnVerifier('AP1', 'MONTANT AUTRES'),
            new ColumnVerifier('AQ1', 'SOUTIRAGE RTE'),
            new ColumnVerifier('AR1', 'DEBUT ACH. FIXE'),
            new ColumnVerifier('AS1', 'FIN ACH. FIXE'),
            new ColumnVerifier('AT1', 'MONTANT ACHEMINEMENT FIXE'),
            new ColumnVerifier('AU1', 'MONTANT ACHEMINEMENT VARIABLE'),
            new ColumnVerifier('AV1', 'MONTANT HTT'),
            new ColumnVerifier('AW1', 'MONTANT CSPE'),
            new ColumnVerifier('AX1', 'MONTANT CTA'),
            new ColumnVerifier('AY1', 'MONTANT TCCFE'),
            new ColumnVerifier('AZ1', 'MONTANT TDCFE'),
            new ColumnVerifier('BA1', 'MONTANT TCFE'),
            new ColumnVerifier('BB1', 'TOTAL HORS TVA'),
            new ColumnVerifier('BC1', 'ASSIETTE TVA TR'),
            new ColumnVerifier('BD1', 'TVA TR'),
            new ColumnVerifier('BE1', 'ASSIETTE TVA TN'),
            new ColumnVerifier('BF1', 'TVA TN'),
            new ColumnVerifier('BG1', 'TOTAL TTC'),
            new ColumnVerifier('BH1', 'TOTAL CONSO KWH'),
            new ColumnVerifier('BI1', 'CONSO BASE/HP/HPH'),
            new ColumnVerifier('BJ1', 'CONSO HC/HCH'),
            new ColumnVerifier('BK1', 'DERNIERE RELEVE REELLE'),
            new ColumnVerifier('BL1', 'TYPE RELEVE'),
            new ColumnVerifier('BM1', 'INDEX REEL BASE/HP/HPH'),
            new ColumnVerifier('BN1', 'INDEX REEL HC/HCH'),
            new ColumnVerifier('BO1', 'DATE THEORIQUE RELEVE'),
            new ColumnVerifier('BP1', 'RELEVE FACTUREE_DEBUT BASE/HP/HPH'),
            new ColumnVerifier('BQ1', 'RELEVE FACTUREE_DEBUT HC/HCH'),
            new ColumnVerifier('BR1', 'RELEVE FACTUREE_FIN BASE')
        ]);

    }

    /**
     * @throws Exception
     * @throws InvalidFileException
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws ImportException
     */
    public function verify(string $filePath, array $invoiceReferencesToReimport, Client $client): void
    {
        $invoiceFileSheet = $this->spreadsheetService->makeXslxSheet($filePath);

        $errors = $this->verifyFileColumns($invoiceFileSheet, $this->fileName, $this->invoiceFileColumns);
        if (!empty($errors)) {
            throw new ImportException($errors);
        }
        if (empty($invoiceReferencesToReimport)) {
            $this->verifyFileInvoicesHaveNotAlreadyBeenImported($invoiceFileSheet, $this->fileName, 'J', 2, $client);
        }
        $this->verifyInvoiceFileData($invoiceFileSheet, $invoiceReferencesToReimport, $client);
    }

    /**
     * @throws Exception
     * @throws ImportException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
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
            if ($checkInvoiceRef && !in_array($this->dataToString($this->getCellValue($sheet, 'J', $rowIndex)), $invoiceReferencesToReimport)) {
                continue;
            }

            $segment = $this->dataToString($this->getCellValue($sheet, 'Z', $rowIndex));
            $connection = $this->dataToString($this->getCellValue($sheet, 'AA', $rowIndex));
            $powerSubscribed = $this->dataToString($this->getCellValue($sheet, 'AD', $rowIndex));
            $powerSubscribedValue = floatval(str_replace(',', '.', $powerSubscribed));
            $totalConsumption = $this->dataToInt($this->getCellValue($sheet, 'BH', $rowIndex));
            $baseConsumption = $this->dataToInt($this->getCellValue($sheet, 'BI', $rowIndex));
            $zipCode = $this->dataToString($this->getCellValue($sheet, 'V', $rowIndex)) ?? '';
            $city = $this->dataToString($this->getCellValue($sheet, 'W', $rowIndex)) ?? '';

            if ($segment !== 'C5') {
                $errors[] = $this->createError("Z$rowIndex", strval($segment), 'C5', $this->fileName);
            }
            if ($connection !== 'BT < 36 kVA') {
                $errors[] = $this->createError("AA$rowIndex", strval($connection), 'BT < 36 kVA', $this->fileName);
            }
            if ($powerSubscribedValue < 0.1 || $powerSubscribedValue > 36) {
                $errors[] = $this->createError("AD$rowIndex", strval($powerSubscribed), $this->translator->trans('between_0_and_36'), $this->fileName);
            }
            if ($totalConsumption !== $baseConsumption) {
                $errors[] = $this->createError("BH$rowIndex", strval($totalConsumption), strval($baseConsumption), $this->fileName);
            }
            if ($zipCode !== $client->getZipCode() && strtoupper($city) !== strtoupper($client->getCity() ?? '')) {
                $errors[] = $this->translator->trans('incorrect_file_wrong_city', [
                    'filename' => '',
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
}