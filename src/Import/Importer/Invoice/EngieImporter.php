<?php

declare(strict_types=1);

namespace App\Import\Importer\Invoice;

use App\Entity\Client;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceTax;
use App\Import\Invoice\EngieTrait;
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

class EngieImporter extends AbstractInvoiceImporter implements InvoiceImporterInterface
{
    use EngieTrait;

    private Worksheet $invoiceSheet;
    private Worksheet $indexSheet;

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
        $this->requiredFiles = [$this->invoiceFile, $this->indexFile];
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

        $checkInvoiceRef = !empty($invoiceReferencesToReimport);
        try {
            $this->invoiceSheet = $extractedFiles[$this->invoiceFile]->getSheet();
            $this->indexSheet = $extractedFiles[$this->indexFile]->getSheet();
            $invoices = [];
            $deliveryPoints = $this->loadExistingDeliveryPoints($this->invoiceSheet, 'V', $this->firstRowIndex, $client);
            $contracts = $this->loadExistingContracts($this->invoiceSheet, 'E', $this->firstRowIndex, $client);
            $indexDatas = $this->loadIndexFile();

            $lastRow = $this->invoiceSheet->getHighestDataRow();
            foreach ($this->invoiceSheet->getRowIterator($this->firstRowIndex) as $row) {
                if ($row->getRowIndex() > $lastRow) {
                    break;
                }
                $rowIndex = strval($row->getRowIndex());
                if (is_null($this->getCellValue($this->invoiceSheet, 'A', $rowIndex))) {
                    continue;
                }

                // skip row if we are reimporting invoices and the current row isn't one of them
                if ($checkInvoiceRef && !in_array($this->dataToString($this->getCellValue($this->invoiceSheet, 'D', $rowIndex)), $invoiceReferencesToReimport)) {
                    continue;
                }

                $data = $this->getRowData(strval($row->getRowIndex()), $indexDatas);
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
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \InvalidArgumentException
     */
    private function getRowData(string $rowIndex, array $indexData = []): ImportData
    {
        $data = new ImportData();
        $data->invoiceDate = $this->dataToDatetime($this->getCellValue($this->invoiceSheet, 'H', $rowIndex));
        $data->invoiceReference = $this->dataToString($this->getCellValue($this->invoiceSheet, 'D', $rowIndex));
        $data->deliveryPointReference = $this->dataToString($this->getCellValue($this->invoiceSheet, 'V', $rowIndex));
        $data->deliveryPointInvoiceReference = $this->dataToString($this->getCellValue($this->invoiceSheet, 'A', $rowIndex));
        $data->contractReference = $this->dataToString($this->getCellValue($this->invoiceSheet, 'E', $rowIndex));
        $data->deliveryPointName = $this->dataToString($this->getCellValue($this->invoiceSheet, 'R', $rowIndex));
        $data->deliveryPointAddress = $this->dataToString($this->getCellValue($this->invoiceSheet, 'S', $rowIndex));
        $data->deliveryPointZipCode = $this->dataToString($this->getCellValue($this->invoiceSheet, 'T', $rowIndex));
        $data->deliveryPointCity = $this->dataToString($this->getCellValue($this->invoiceSheet, 'U', $rowIndex));
        $data->powerSubscribed = $this->dataToString($this->getCellValue($this->invoiceSheet, 'AG', $rowIndex));
        $data->cspe = $this->amountToInt($this->getCellValue($this->invoiceSheet, 'AQ', $rowIndex));
        $data->cta = $this->amountToInt($this->getCellValue($this->invoiceSheet, 'AT', $rowIndex));
        $data->tcfe = $this->amountToInt($this->getCellValue($this->invoiceSheet, 'AR', $rowIndex));
        $data->totalTaxExcluded = $this->amountToInt($this->getCellValue($this->invoiceSheet, 'AU', $rowIndex));
        $data->totalTaxIncluded = $this->amountToInt($this->getCellValue($this->invoiceSheet, 'AY', $rowIndex));
        $data->totalTVA = $this->amountToInt($this->getCellValue($this->invoiceSheet, 'AX', $rowIndex));

        $turpeFixed = $this->getCellValue($this->invoiceSheet, 'AH', $rowIndex);
        $turpeVariable = $this->getCellValue($this->invoiceSheet, 'AI', $rowIndex);
        if (!is_null($turpeFixed) && !is_null($turpeVariable)) {
            $data->turpe = $this->amountToInt($turpeFixed) + $this->amountToInt($turpeVariable);
        }
        
        $data->consumptionStartedDate = $this->dataToDatetime($this->getCellValue($this->invoiceSheet, 'Y', $rowIndex));
        $data->consumptionFinishedDate = $this->dataToDatetime($this->getCellValue($this->invoiceSheet, 'Z', $rowIndex));

        $data->consumptionQuantity = $this->dataToInt($this->getCellValue($this->invoiceSheet, 'AC', $rowIndex));
        $data->consumptionTotal = $this->amountToInt($this->getCellValue($this->invoiceSheet, 'AJ', $rowIndex));
        $data->consumptionIndexFinish = $this->getIndexFinish($data, $indexData);

        $data->subscriptionStartedDate = $data->consumptionStartedDate;
        $data->subscriptionFinishedDate = $data->consumptionFinishedDate;
        $data->subscriptionQuantity = $this->amountToInt($this->getCellValue($this->invoiceSheet, 'AA', $rowIndex));

        $data->readingType = DeliveryPointInvoice::TYPE_REAL;

        return $data;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function loadIndexFile(): array
    {
        $lastRow = $this->indexSheet->getHighestDataRow();
        $indexDatas = [];
        foreach ($this->indexSheet->getRowIterator(13) as $row) {
            if ($row->getRowIndex() > $lastRow) {
                break;
            }
            $rowIndex = strval($row->getRowIndex());
            $dpInvoiceRef = $this->dataToString($this->getCellValue($this->indexSheet, 'H', $rowIndex));
            if (!$dpInvoiceRef) {
                continue;
            }

            $index = [
                'finish_at' => $this->dataToDatetime($this->getCellValue($this->indexSheet, 'C', $rowIndex)),
                'finish' => $this->dataToInt($this->getCellValue($this->indexSheet, 'D', $rowIndex))
            ];

            if (empty($indexDatas[$dpInvoiceRef])) {
                $indexDatas[$dpInvoiceRef] = [$index];
            } else {
                $indexDatas[$dpInvoiceRef][] = $index;
            }
        }
        return $indexDatas;
    }

    private function getIndexFinish(ImportData $data, array $indexData): ?int
    {
        $finish = null;

        if (array_key_exists($data->deliveryPointInvoiceReference, $indexData)) {
            foreach ($indexData[$data->deliveryPointInvoiceReference] as $index) {
                if ($data->consumptionFinishedDate === $index['finish_at']) {
                    $finish = $index['finish'];
                }
            }
        }

        return $finish;
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