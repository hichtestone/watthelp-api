<?php

declare(strict_types=1);

namespace App\Import\Importer\Invoice;

use App\Entity\Client;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceTax;
use App\Entity\Pricing;
use App\Exceptions\InvalidFileException;
use App\Import\Invoice\EdfTrait;
use App\Manager\ContractManager;
use App\Manager\DeliveryPointManager;
use App\Manager\InvoiceManager;
use App\Manager\Invoice\AnomalyManager;
use App\Model\Import\Invoice\ImportData;
use App\Service\SpreadsheetService;
use App\Service\ZipService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Contracts\Translation\TranslatorInterface;

class EdfImporter extends AbstractInvoiceImporter implements InvoiceImporterInterface
{
    use EdfTrait;

    private Worksheet $sitesElecSheet;
    private Worksheet $sitesElecPhsSheet;
    private Worksheet $contractualInformationSheet;

    public function __construct(
        SpreadsheetService $spreadsheetService,
        ZipService $zipService,
        DeliveryPointManager $deliveryPointManager,
        InvoiceManager $invoiceManager,
        EntityManagerInterface $entityManager,
        ContractManager $contractManager,
        AnomalyManager $anomalyManager,
        TranslatorInterface $translator
    ) {
        $this->requiredFiles = [$this->sitesElecFile, $this->sitesElecPhsFile, $this->contractualInformationFile];
        $this->spreadsheetService = $spreadsheetService;
        $this->zipService = $zipService;
        $this->deliveryPointManager = $deliveryPointManager;
        $this->invoiceManager = $invoiceManager;
        $this->entityManager = $entityManager;
        $this->contractManager = $contractManager;
        $this->anomalyManager = $anomalyManager;
        $this->translator = $translator;
    }


    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function import(string $zipPath, Client $client, array $invoiceReferencesToReimport): array
    {
        $extractedFiles = $this->extract($zipPath);

        try {
            $this->sitesElecSheet = $extractedFiles[$this->sitesElecFile]->getSheet();
            $this->sitesElecPhsSheet = $extractedFiles[$this->sitesElecPhsFile]->getSheet();
            $this->contractualInformationSheet = $extractedFiles[$this->contractualInformationFile]->getSheet();
            $invoices = [];
            $deliveryPoints = $this->loadExistingDeliveryPoints($this->sitesElecSheet, 'D', $this->firstRowIndex, $client);
            $contracts = $this->loadExistingContracts($this->contractualInformationSheet, 'L', $this->firstRowIndex, $client);
            $contractualInformationData = $this->loadContractualInformation();
            $sitesElecData = $this->loadSitesElec($contractualInformationData, $invoiceReferencesToReimport);
            $importData = $this->loadSitesElecPhs($sitesElecData, $invoiceReferencesToReimport);

            foreach ($importData as $data) {
                $this->insertRowData($data, $deliveryPoints, $invoices, $contracts, $client, $this->provider);
            }
            $this->checkDeliveryPointPowerChange($deliveryPoints);
            $this->entityManager->flush();
            foreach ($this->anomalies as $anomaly) {
                $this->entityManager->persist($anomaly);
            }
            foreach ($invoices as $invoice) {
                $this->entityManager->persist($invoice);
            }
            $this->entityManager->flush();

            return [$invoices, $this->anomalies];

        } finally {
            foreach ($extractedFiles as $extractedFile) {
                unlink($extractedFile->getPath());
            }
        }
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function loadContractualInformation(): array
    {
        $result = [];

        $lastRow = $this->contractualInformationSheet->getHighestDataRow();
        foreach ($this->contractualInformationSheet->getRowIterator($this->firstRowIndex) as $row) {
            if ($row->getRowIndex() > $lastRow) {
                break;
            }
            $rowIndex = strval($row->getRowIndex());
            if (is_null($this->getCellValue($this->contractualInformationSheet, 'A', $rowIndex))) {
                continue;
            }
            $deliveryPointReference = $this->dataToString($this->getCellValue($this->contractualInformationSheet, 'D', $rowIndex));
            $row = [
                'deliveryPointName' => $this->dataToString($this->getCellValue($this->contractualInformationSheet, 'B', $rowIndex)),
                'deliveryPointReference' => $deliveryPointReference,
                'deliveryPointAddress' => $this->dataToString($this->getCellValue($this->contractualInformationSheet, 'O', $rowIndex)),
                'deliveryPointZipCode' => $this->dataToString($this->getCellValue($this->contractualInformationSheet, 'P', $rowIndex)),
                'deliveryPointCity' => $this->dataToString($this->getCellValue($this->contractualInformationSheet, 'Q', $rowIndex)),
                'meterReference' => $this->dataToString($this->getCellValue($this->contractualInformationSheet, 'F', $rowIndex)) ?? '',
                'contractReference' => $this->dataToString($this->getCellValue($this->contractualInformationSheet, 'L', $rowIndex)),
                'contractFinishedDate' => $this->dataToDatetime($this->getCellValue($this->contractualInformationSheet, 'M', $rowIndex)),
                'contractType' => $this->dataToString($this->getCellValue($this->contractualInformationSheet, 'K', $rowIndex)) === 'Tarif réglementé' ? Pricing::TYPE_REGULATED : Pricing::TYPE_NEGOTIATED
            ];
            $result[$deliveryPointReference] = $row;
        }

        return $result;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function loadSitesElec(array $contractualInformationData, array $invoiceReferencesToReimport): array
    {
        $importData = [];

        $lastRow = $this->sitesElecSheet->getHighestDataRow();
        $checkInvoiceRef = !empty($invoiceReferencesToReimport);
        foreach ($this->sitesElecSheet->getRowIterator($this->firstRowIndex) as $row) {
            if ($row->getRowIndex() > $lastRow) {
                break;
            }
            $rowIndex = strval($row->getRowIndex());
            if (is_null($this->getCellValue($this->sitesElecSheet, 'A', $rowIndex))) {
                continue;
            }

            $invoiceReference = $this->dataToString($this->getCellValue($this->sitesElecSheet, 'B', $rowIndex));
            // skip row if we are reimporting invoices and the current row isn't one of them
            if ($checkInvoiceRef && !in_array($invoiceReference, $invoiceReferencesToReimport)) {
                continue;
            }

            $data = new ImportData();
            $data->deliveryPointReference = $this->dataToString($this->getCellValue($this->sitesElecSheet, 'D', $rowIndex));
            
            // add data from information_contractuelles file
            if (isset($contractualInformationData[$data->deliveryPointReference])) {
                $data->deliveryPointName = $contractualInformationData[$data->deliveryPointReference]['deliveryPointName'];
                $data->deliveryPointAddress = $contractualInformationData[$data->deliveryPointReference]['deliveryPointAddress'];
                $data->deliveryPointZipCode = $contractualInformationData[$data->deliveryPointReference]['deliveryPointZipCode'];
                $data->deliveryPointCity = $contractualInformationData[$data->deliveryPointReference]['deliveryPointCity'];
                $data->meterReference = $contractualInformationData[$data->deliveryPointReference]['meterReference'];
                $data->contractReference = $contractualInformationData[$data->deliveryPointReference]['contractReference'];
                $data->contractFinishedDate = $contractualInformationData[$data->deliveryPointReference]['contractFinishedDate'];
                $data->contractType = $contractualInformationData[$data->deliveryPointReference]['contractType'];
            }

            $data->invoiceDate = $this->dataToDatetime($this->getCellValue($this->sitesElecSheet, 'A', $rowIndex));
            $data->invoiceReference = $invoiceReference;
            $data->cspe = $this->amountToInt($this->getCellValue($this->sitesElecSheet, 'AA', $rowIndex));
            $data->cta = $this->amountToInt($this->getCellValue($this->sitesElecSheet, 'AB', $rowIndex));
            $data->tcfe = $this->amountToInt($this->getCellValue($this->sitesElecSheet, 'Z', $rowIndex));
            $data->totalTaxExcluded = $this->amountToInt($this->getCellValue($this->sitesElecSheet, 'AC', $rowIndex));
            $data->totalTaxIncluded = $this->amountToInt($this->getCellValue($this->sitesElecSheet, 'AG', $rowIndex));
            $data->totalTVA = $this->amountToInt($this->getCellValue($this->sitesElecSheet, 'AF', $rowIndex));
            $data->turpe = $this->amountToInt($this->getCellValue($this->sitesElecSheet, 'X', $rowIndex));
            
            $data->subscriptionTotal = $this->amountToInt($this->getCellValue($this->sitesElecSheet, 'O', $rowIndex));
            $data->consumptionStartedDate = $this->dataToDatetime($this->getCellValue($this->sitesElecSheet, 'M', $rowIndex));
            $data->consumptionFinishedDate = $this->dataToDatetime($this->getCellValue($this->sitesElecSheet, 'N', $rowIndex));
            $data->consumptionQuantity = $this->dataToInt($this->getCellValue($this->sitesElecSheet, 'R', $rowIndex));
            $data->consumptionTotal = $this->amountToInt($this->getCellValue($this->sitesElecSheet, 'V', $rowIndex));
            $data->consumptionUnitPrice = $this->amountToInt($this->getCellValue($this->sitesElecSheet, 'S', $rowIndex), true);


            $data->readingType = $this->dataToString($this->getCellValue($this->sitesElecSheet, 'L', $rowIndex)) === 'Relevé' ? DeliveryPointInvoice::TYPE_REAL : DeliveryPointInvoice::TYPE_ESTIMATED;

            $importData[$data->invoiceReference.'-'.$data->deliveryPointReference] = $data;
        }

        return $importData;
    }

    /**
     * @throws InvalidFileException
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function loadSitesElecPhs(array $importData, array $invoiceReferencesToReimport): array
    {
        $lastRow = $this->sitesElecPhsSheet->getHighestDataRow();
        $checkInvoiceRef = !empty($invoiceReferencesToReimport);
        foreach ($this->sitesElecPhsSheet->getRowIterator($this->firstRowIndex) as $row) {
            if ($row->getRowIndex() > $lastRow) {
                break;
            }
            $rowIndex = strval($row->getRowIndex());
            if (is_null($this->getCellValue($this->sitesElecPhsSheet, 'A', $rowIndex))) {
                continue;
            }
            $invoiceReference = $this->dataToString($this->getCellValue($this->sitesElecPhsSheet, 'B', $rowIndex));
            // skip row if we are reimporting invoices and the current row isn't one of them
            if ($checkInvoiceRef && !in_array($invoiceReference, $invoiceReferencesToReimport)) {
                continue;
            }

            $deliveryPointReference = $this->dataToString($this->getCellValue($this->sitesElecPhsSheet, 'D', $rowIndex));
            $ref = $invoiceReference.'-'.$deliveryPointReference;

            if (!isset($importData[$ref])) {
                throw new InvalidFileException($this->translator->trans(
                    'could_not_find_delivery_point_corresponding_to_invoice',
                    [
                        'delivery_point' => $deliveryPointReference,
                        'invoice' => $invoiceReference
                    ]
                ));
            }
            $data = $importData[$ref];

            $data->powerSubscribed = str_replace(',', '.', $this->dataToString($this->getCellValue($this->sitesElecPhsSheet, 'O', $rowIndex)));

            $data->consumptionIndexStartedDate = $this->dataToDatetime($this->getCellValue($this->sitesElecPhsSheet, 'T', $rowIndex));
            $data->consumptionIndexFinishedDate = $this->dataToDatetime($this->getCellValue($this->sitesElecPhsSheet, 'V', $rowIndex));
            $data->consumptionIndexStart = $this->dataToInt($this->getCellValue($this->sitesElecPhsSheet, 'S', $rowIndex));
            $data->consumptionIndexFinish = $this->dataToInt($this->getCellValue($this->sitesElecPhsSheet, 'U', $rowIndex));
        }

        return $importData;
    }

    protected function importTaxes(ImportData $data, DeliveryPointInvoice $dpi): ArrayCollection
    {
        $taxes = new ArrayCollection();

        $taxes->add($this->importTax($data, $dpi, InvoiceTax::TYPE_TAX_CSPE, $data->cspe, $data->consumptionQuantity));
        $taxes->add($this->importTax($data, $dpi, InvoiceTax::TYPE_TAX_CTA, $data->cta, $data->consumptionQuantity));
        $taxes->add($this->importTax($data, $dpi, InvoiceTax::TYPE_TAX_TCFE, $data->tcfe, $data->consumptionQuantity));

        return $taxes;
    }

}