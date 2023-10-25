<?php

declare(strict_types=1);

namespace App\Import\Importer\Invoice;

use App\Entity\Client;
use App\Entity\Contract;
use App\Entity\DeliveryPoint;
use App\Entity\Invoice;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceConsumption;
use App\Entity\Invoice\InvoiceSubscription;
use App\Entity\Invoice\InvoiceTax;
use App\Import\SpreadsheetReaderTrait;
use App\Manager\ContractManager;
use App\Manager\DeliveryPointManager;
use App\Manager\InvoiceManager;
use App\Manager\Invoice\AnomalyManager;
use App\Model\Import\Invoice\ImportData;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractInvoiceImporter
{
    use SpreadsheetReaderTrait;

    protected $anomalies = [];
    protected EntityManagerInterface $entityManager;
    protected DeliveryPointManager $deliveryPointManager;
    protected InvoiceManager $invoiceManager;
    protected ContractManager $contractManager;
    protected AnomalyManager $anomalyManager;
    protected TranslatorInterface $translator;

    protected function createAnomaly(string $reference, string $content): void
    {
        if (array_key_exists($reference, $this->anomalies)) {
            return;
        }

        $anomalyExists = $this->anomalyManager->count(['reference' => $reference]) !== 0;
        if ($anomalyExists) {
            return;
        }
        
        $anomaly = new Anomaly();
        $anomaly->setReference($reference);
        $anomaly->setType(Anomaly::TYPE_DELIVERY_POINT_CHANGE);
        $anomaly->setStatus(Anomaly::STATUS_PROCESSING);
        $anomaly->setContent($content);

        $this->anomalies[$reference] = $anomaly;
    }

    protected function makeAnomalyReference(DeliveryPoint $dp, Invoice $invoice, string $type): string
    {
        return $dp->getReference().$invoice->getReference().$type;
    }

    protected function checkDeliveryPointPowerChange(array $deliveryPoints): void
    {
        $addNullPowerWarning = function (DeliveryPoint $dp, Invoice $invoice) {
            $reference = $this->makeAnomalyReference($dp, $invoice, Anomaly::DELIVERY_POINT_CHANGE_POWER_NULL);
            $this->createAnomaly($reference, $this->translator->trans(
                'delivery_point_power_missing_on_invoice',
                [
                    'delivery_point' => $dp->getReference(),
                    'invoice' => $invoice->getReference()
                ]
            ));
        };
        foreach ($deliveryPoints as $deliveryPoint) {
            $dpi = $deliveryPoint->getDeliveryPointInvoicesSorted()->toArray();
            for ($i = 0, $max = count($dpi)-1; $i < $max; ++$i) {
                if (is_null($dpi[$i]->getPowerSubscribed()) || is_null($dpi[$i+1]->getPowerSubscribed())) {
                    if (is_null($dpi[$i]->getPowerSubscribed())) {
                        $addNullPowerWarning($deliveryPoint, $dpi[$i]->getInvoice());
                    }
                    if (is_null($dpi[$i+1]->getPowerSubscribed())) {
                        $addNullPowerWarning($deliveryPoint, $dpi[$i+1]->getInvoice());
                    }
                    continue;
                }
                if (floatval($dpi[$i]->getPowerSubscribed()) !== floatval($dpi[$i+1]->getPowerSubscribed())) {
                    $reference = $this->makeAnomalyReference($deliveryPoint, $dpi[$i+1]->getInvoice(), Anomaly::DELIVERY_POINT_CHANGE_POWER_CHANGED);
                    $this->createAnomaly($reference, $this->translator->trans(
                        'delivery_point_changed',
                        [
                            'attribute' => 'power',
                            'delivery_point' => $deliveryPoint->getReference(),
                            'old_delivery_point_value' => strval($dpi[$i]->getPowerSubscribed()).'kW',
                            'old_invoice' => $dpi[$i]->getInvoice()->getReference(),
                            'new_delivery_point_value' => strval($dpi[$i+1]->getPowerSubscribed()).'kW',
                            'new_invoice' => $dpi[$i+1]->getInvoice()->getReference()
                        ]
                    ));
                }
            }
        }
    }

    protected function importDeliveryPoint(ImportData $data, Client $client, ?DeliveryPoint $deliveryPoint): DeliveryPoint
    {
        $address = sprintf('%s, %s %s', $data->deliveryPointAddress, $data->deliveryPointZipCode, $data->deliveryPointCity);

        if ($deliveryPoint) {
            $dpi = $deliveryPoint->getDeliveryPointInvoicesSorted()->last();
            if ($dpi) {
                // don't overwrite delivery point if we're importing an older invoice
                if ($data->invoiceDate < $dpi->getInvoice()->getEmittedAt()) {
                    return $deliveryPoint;
                }
                if ($deliveryPoint->getAddress() !== $address) {
                    $reference = $data->deliveryPointReference.$data->invoiceReference.Anomaly::DELIVERY_POINT_CHANGE_ADDRESS_CHANGED;
                    $this->createAnomaly($reference, $this->translator->trans(
                        'delivery_point_changed',
                        [
                            'attribute' => 'address',
                            'delivery_point' => $deliveryPoint->getReference(),
                            'old_delivery_point_value' => $deliveryPoint->getAddress(),
                            'old_invoice' => $dpi->getInvoice()->getReference(),
                            'new_delivery_point_value' => $address,
                            'new_invoice' => $data->invoiceReference
                        ]
                    ));
                }
            }
        }

        $deliveryPointAlreadyExists = $deliveryPoint !== null;
        $deliveryPoint ??= new DeliveryPoint();
        $deliveryPoint->setClient($client);
        $deliveryPoint->setName($data->deliveryPointName ?? '');
        $deliveryPoint->setReference($data->deliveryPointReference);
        $deliveryPoint->setAddress($address);
        $deliveryPoint->setMeterReference($data->meterReference ?? '');
        if (!is_null($data->powerSubscribed) && $data->powerSubscribed !== '') {
            $deliveryPoint->setPower($data->powerSubscribed);
        }
        if (!$deliveryPointAlreadyExists) {
            $deliveryPoint->setCreationMode(DeliveryPoint::CREATION_MODE_INVOICE_IMPORT);
        }

        return $deliveryPoint;
    }

    protected function importContract(ImportData $data, Client $client, DeliveryPoint $deliveryPoint, string $provider): Contract
    {
        $contract = new Contract();
        $contract->setClient($client);
        $contract->setProvider($provider);
        $contract->setType($data->contractType);
        $contract->setReference($data->contractReference);
        $contract->setStartedAt($data->invoiceDate ?? new \DateTime());
        $contract->addDeliveryPoint($deliveryPoint);

        return $contract;
    }

    protected function importInvoice(ImportData $data, Client $client): Invoice
    {
        $invoice = new Invoice();
        $invoice->setClient($client);
        $invoice->setReference($data->invoiceReference);
        $invoice->setEmittedAt($data->invoiceDate);
        $invoice->setAmountHT(0);
        $invoice->setAmountTVA(0);
        $invoice->setAmountTTC(0);

        return $invoice;
    }

    protected function importDeliveryPointInvoice(ImportData $data, DeliveryPoint $dp, Invoice $invoice): DeliveryPointInvoice
    {
        $deliveryPointInvoice = null;
        foreach ($dp->getDeliveryPointInvoices() as $dpi) {
            if ($dpi->getInvoice()->getReference() === $invoice->getReference()) {
                $deliveryPointInvoice = $dpi;
                break;
            }
        }

        $deliveryPointInvoice ??= new DeliveryPointInvoice();

        $deliveryPointInvoice->setDeliveryPoint($dp);
        $deliveryPointInvoice->setInvoice($invoice);
        $deliveryPointInvoice->setAmountHT($data->totalTaxExcluded);
        $deliveryPointInvoice->setAmountTVA($data->totalTVA);
        $deliveryPointInvoice->setAmountTTC($data->totalTaxIncluded);
        $deliveryPointInvoice->setPowerSubscribed($data->powerSubscribed);
        if (in_array($data->readingType, DeliveryPointInvoice::AVAILABLE_TYPES)) {
            $deliveryPointInvoice->setType($data->readingType);
        }

        return $deliveryPointInvoice;
    }

    protected function importSubscription(ImportData $data, DeliveryPointInvoice $dpi): InvoiceSubscription
    {
        $subscription = $dpi->getSubscription() ?? new InvoiceSubscription();
        $subscription->setDeliveryPointInvoice($dpi);
        $subscription->setTotal($data->subscriptionTotal ?? $data->turpe);
        $subscription->setStartedAt($data->subscriptionStartedDate);
        $subscription->setFinishedAt($data->subscriptionFinishedDate);

        if ($data->subscriptionQuantity) {
            $subscription->setQuantity($data->subscriptionQuantity);
        } else if ($data->subscriptionStartedDate && $data->subscriptionFinishedDate) {
            $finishedAtSubscriptionForDiff = clone $data->subscriptionFinishedDate;
            $diff = $finishedAtSubscriptionForDiff->add(new \DateInterval('P1D'))->diff($data->subscriptionStartedDate);
            $subscription->setQuantity($diff->m);
        }

        return $subscription;
    }

    protected function importConsumption(ImportData $data, DeliveryPointInvoice $dpi): InvoiceConsumption
    {
        $consumption = $dpi->checkAndGetConsumption() ?? new InvoiceConsumption();
        $consumption->setDeliveryPointInvoice($dpi);
        if ($data->consumptionStartedDate) {
            $consumption->setStartedAt($data->consumptionStartedDate);
            $consumption->setIndexStartedAt($data->consumptionStartedDate);
        }
        if ($data->consumptionFinishedDate) {
            $consumption->setFinishedAt($data->consumptionFinishedDate);
            $consumption->setIndexFinishedAt($data->consumptionFinishedDate);
        }
        // negate consumption quantity if amount is negative and quantity isn't
        if ($data->consumptionTotal && $data->consumptionQuantity &&
            $data->consumptionTotal < 0 && $data->consumptionQuantity > 0) {
            $data->consumptionQuantity = -$data->consumptionQuantity;
        }
        $consumption->setIndexStart($data->consumptionIndexStart);
        $consumption->setIndexFinish($data->consumptionIndexFinish);
        $consumption->setQuantity($data->consumptionQuantity);
        $consumption->setTotal($data->consumptionTotal);
        $consumption->setUnitPrice($data->consumptionUnitPrice);
        
        return $consumption;
    }

    protected function importTax(ImportData $data, DeliveryPointInvoice $dpi, string $type, ?int $total, ?int $quantity): InvoiceTax
    {
        $existingTax = $dpi->getTaxes()->filter(fn ($tax) => $tax->getType() === $type);
        $tax = !$existingTax->isEmpty() ? $existingTax->first() : new InvoiceTax();
        $tax->addDeliveryPointInvoice($dpi);
        $tax->setType($type);
        if ($data->consumptionStartedDate) {
            $tax->setStartedAt($data->consumptionStartedDate);
        }
        if ($data->consumptionFinishedDate) {
            $tax->setFinishedAt($data->consumptionFinishedDate);
        }
        $tax->setTotal($total);

        if ($type === InvoiceTax::TYPE_TAX_CTA) {
            $tax->setUnitPrice(InvoiceTax::CTA_UNIT_PRICE);
        } else {
            $tax->setQuantity($data->consumptionQuantity);
            if (!is_null($total) && $quantity) {
                $tax->setUnitPrice(intval(round($total / $quantity)));    
            }
        }

        return $tax;
    }

    protected function insertRowData(ImportData $data, array &$deliveryPoints, array &$invoices, array &$contracts, Client $client, string $provider): void
    {
        $deliveryPoint = $deliveryPoints[$data->deliveryPointReference] ?? null;
        $deliveryPoint = $this->importDeliveryPoint($data, $client, $deliveryPoint);

        if (array_key_exists($data->invoiceReference, $invoices)) {
            $invoice = $invoices[$data->invoiceReference];
        } else {
            $invoice = $this->importInvoice($data, $client);
            $invoices[$data->invoiceReference] = $invoice;
        }

        if ($data->contractReference) {
            if (array_key_exists($data->contractReference, $contracts)) {
                $contract = $contracts[$data->contractReference];
            } else {
                $contract = $this->importContract($data, $client, $deliveryPoint, $provider);
                $contracts[$data->contractReference] = $contract;
            }
            $deliveryPoint->setContract($contract);
            $this->entityManager->persist($contract);
        }

        $deliveryPointInvoice = $this->importDeliveryPointInvoice($data, $deliveryPoint, $invoice);
        $invoice->addDeliveryPointInvoice($deliveryPointInvoice);
        $deliveryPoint->addDeliveryPointInvoice($deliveryPointInvoice);


        $subscription = $this->importSubscription($data, $deliveryPointInvoice);
        $deliveryPointInvoice->setSubscription($subscription);

        $consumption = $this->importConsumption($data, $deliveryPointInvoice);
        $deliveryPointInvoice->setConsumption($consumption);

        $taxes = $this->importTaxes($data, $deliveryPointInvoice);
        $deliveryPointInvoice->setTaxes($taxes);

        $invoice->setAmountHT($invoice->getAmountHT()+($data->totalTaxExcluded ?? 0));
        $invoice->setAmountTVA($invoice->getAmountTVA()+($data->totalTVA ?? 0));
        $invoice->setAmountTTC($invoice->getAmountTTC()+($data->totalTaxIncluded ?? 0));

        $deliveryPoints[$deliveryPoint->getReference()] = $deliveryPoint;
        $this->entityManager->persist($deliveryPoint);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Calculation\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function loadExistingEntityByReference(Worksheet $sheet, string $column, int $firstRowIndex, Client $client, $manager): array
    {
        $firstRowIndex = strval($firstRowIndex);
        $entities = [];
        
        $lastRowIndex = $sheet->getHighestDataRow();
        $entityReferencesInFile = $sheet->rangeToArray($column.$firstRowIndex.':'.$column.strval($lastRowIndex));
        $entityReferencesInFile = array_unique(array_map(fn($item) => $item[0], $entityReferencesInFile));

        $existingEntities = $manager->findByFilters($client, ['references' => $entityReferencesInFile]);

        foreach ($existingEntities as $existingEntity) {
            $entities[$existingEntity->getReference()] = $existingEntity;
        }

        return $entities;
    }

    /**
     * @return DeliveryPoint[] - indexed by their reference
     * @throws \PhpOffice\PhpSpreadsheet\Calculation\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function loadExistingDeliveryPoints(Worksheet $sheet, string $column, int $firstRowIndex, Client $client): array
    {
        $deliveryPoints = $this->loadExistingEntityByReference($sheet, $column, $firstRowIndex, $client, $this->deliveryPointManager);
        foreach ($deliveryPoints as &$deliveryPoint) {
            $deliveryPoint->sortDeliveryPointInvoices();
        }
        return $deliveryPoints;
    }

    /**
     * @return Contract[] - index by their reference
     * @throws \PhpOffice\PhpSpreadsheet\Calculation\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function loadExistingContracts(Worksheet $sheet, string $column, int $firstRowIndex, Client $client): array
    {
        return $this->loadExistingEntityByReference($sheet, $column, $firstRowIndex, $client, $this->contractManager);
    }
}