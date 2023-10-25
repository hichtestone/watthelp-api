<?php

declare(strict_types=1);

namespace App\Import\Importer\Invoice;

use App\Entity\Client;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceTax;
use App\Import\Invoice\DirectEnergieTrait;
use App\Manager\DeliveryPointManager;
use App\Manager\InvoiceManager;
use App\Manager\Invoice\AnomalyManager;
use App\Model\Import\Invoice\ImportData;
use App\Service\SpreadsheetService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Contracts\Translation\TranslatorInterface;

class DirectEnergieImporter extends AbstractInvoiceImporter implements InvoiceImporterInterface
{
    use DirectEnergieTrait;

    private Worksheet $sheet;

    public function __construct(
        SpreadsheetService $spreadsheetService,
        DeliveryPointManager $deliveryPointManager,
        InvoiceManager $invoiceManager,
        EntityManagerInterface $entityManager,
        AnomalyManager $anomalyManager,
        TranslatorInterface $translator
    ) {
        $this->spreadsheetService = $spreadsheetService;
        $this->deliveryPointManager = $deliveryPointManager;
        $this->invoiceManager = $invoiceManager;
        $this->entityManager = $entityManager;
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
    public function import(string $filePath, Client $client, array $invoiceReferencesToReimport): array
    {
        $this->sheet = $this->spreadsheetService->makeXslxSheet($filePath);

        $invoices = [];
        $deliveryPoints = $this->loadExistingDeliveryPoints($this->sheet, 'T', $this->firstRowIndex, $client);

        $contracts = [];

        $lastRow = $this->sheet->getHighestDataRow();

        $checkInvoiceRef = !empty($invoiceReferencesToReimport);
        foreach ($this->sheet->getRowIterator($this->firstRowIndex) as $row) {
            if ($row->getRowIndex() > $lastRow) {
                break;
            }
            $rowIndex = strval($row->getRowIndex());
            if (is_null($this->getCellValue($this->sheet, 'A', $rowIndex))) {
                continue;
            }

            // skip row if we are reimporting invoices and the current row isn't one of them
            if ($checkInvoiceRef && !in_array($this->dataToString($this->getCellValue($this->sheet, 'J', $rowIndex)), $invoiceReferencesToReimport)) {
                continue;
            }

            $data = $this->getRowData(strval($row->getRowIndex()));
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
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \InvalidArgumentException
     */
    private function getRowData(string $rowIndex): ImportData
    {
        $data = new ImportData();
        $data->invoiceDate = $this->dataToDatetime($this->getCellValue($this->sheet, 'K', $rowIndex));
        $data->invoiceReference = $this->dataToString($this->getCellValue($this->sheet, 'J', $rowIndex));
        $data->deliveryPointReference = $this->dataToString($this->getCellValue($this->sheet, 'T', $rowIndex));
        $data->deliveryPointName = $this->dataToString($this->getCellValue($this->sheet, 'Q', $rowIndex));
        $data->deliveryPointAddress = trim($this->getCellValue($this->sheet, 'U', $rowIndex));
        $data->deliveryPointZipCode = $this->dataToString($this->getCellValue($this->sheet, 'V', $rowIndex));
        $data->deliveryPointCity = $this->dataToString($this->getCellValue($this->sheet, 'W', $rowIndex));
        $data->powerSubscribed = $this->dataToString($this->getCellValue($this->sheet, 'AD', $rowIndex));
        $data->meterReference = $this->dataToString($this->getCellValue($this->sheet, 'Y', $rowIndex));
        $data->cspe = $this->amountToInt($this->getCellValue($this->sheet, 'AW', $rowIndex));
        $data->cta = $this->amountToInt($this->getCellValue($this->sheet, 'AX', $rowIndex));
        $data->tccfe = $this->amountToInt($this->getCellValue($this->sheet, 'AY', $rowIndex));
        $data->tdcfe = $this->amountToInt($this->getCellValue($this->sheet, 'AZ', $rowIndex));
        $data->totalTaxExcluded = $this->amountToInt($this->getCellValue($this->sheet, 'BB', $rowIndex));
        $data->totalTaxIncluded = $this->amountToInt($this->getCellValue($this->sheet, 'BG', $rowIndex));
        
        $tvaReduced = $this->getCellValue($this->sheet, 'BD', $rowIndex);
        $tvaNormal = $this->getCellValue($this->sheet, 'BF', $rowIndex);
        if (!is_null($tvaReduced) && !is_null($tvaNormal)) {
            $data->totalTVA = $this->amountToInt($tvaReduced) + $this->amountToInt($tvaNormal);
        }

        $turpeFixed = $this->getCellValue($this->sheet, 'AT', $rowIndex);
        $turpeVariable = $this->getCellValue($this->sheet, 'AU', $rowIndex);
        if (!is_null($turpeFixed) && !is_null($turpeVariable)) {
            $data->turpe = $this->amountToInt($turpeFixed) + $this->amountToInt($turpeVariable);
        }
        
        $data->subscriptionStartedDate = $this->dataToDatetime($this->getCellValue($this->sheet, 'AE', $rowIndex));
        $data->subscriptionFinishedDate = $this->dataToDatetime($this->getCellValue($this->sheet, 'AF', $rowIndex));

        $data->consumptionQuantity = $this->getCellValue($this->sheet, 'BH', $rowIndex);
        $data->consumptionTotal = $this->amountToInt($this->getCellValue($this->sheet, 'AJ', $rowIndex));
        $data->consumptionStartedDate = $this->dataToDatetime($this->getCellValue($this->sheet, 'AH', $rowIndex));
        $data->consumptionFinishedDate = $this->dataToDatetime($this->getCellValue($this->sheet, 'AI', $rowIndex));
        $data->consumptionIndexStart = $this->dataToInt($this->getCellValue($this->sheet, 'BP', $rowIndex));
        $data->consumptionIndexFinish = $this->dataToInt($this->getCellValue($this->sheet, 'BR', $rowIndex));

        $data->readingType = $this->dataToString($this->getCellValue($this->sheet, 'BL', $rowIndex)) === 'Relevé normal' ? DeliveryPointInvoice::TYPE_REAL : DeliveryPointInvoice::TYPE_ESTIMATED;

        return $data;
    }

    protected function importTaxes(ImportData $data, DeliveryPointInvoice $dpi): ArrayCollection
    {
        $taxes = new ArrayCollection();

        $taxes->add($this->importTax($data, $dpi, InvoiceTax::TYPE_TAX_CSPE, $data->cspe, $data->consumptionQuantity));
        $taxes->add($this->importTax($data, $dpi, InvoiceTax::TYPE_TAX_CTA, $data->cta, $data->consumptionQuantity));
        $taxes->add($this->importTax($data, $dpi, InvoiceTax::TYPE_TAX_TDCFE, $data->tdcfe, $data->consumptionQuantity));
        $taxes->add($this->importTax($data, $dpi, InvoiceTax::TYPE_TAX_TCCFE, $data->tccfe, $data->consumptionQuantity));

        return $taxes;
    }

}