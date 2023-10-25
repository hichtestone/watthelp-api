<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice\Consumption;

use App\Analyzer\AbstractAnalyzer;
use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Exceptions\IgnoreException;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Service\ConsumptionService;
use App\Service\LogService;

class QuantityAnalyzer extends AbstractAnalyzer implements AnalyzerInterface
{
    protected ConsumptionService $consumptionService;
    private bool $hasAnomaly = false;

    public function __construct(
        TranslationManager $translationManager,
        LogService $logger,
        ConsumptionService $consumptionService
    ) {
        parent::__construct($translationManager, $logger);
        $this->consumptionService = $consumptionService;
    }

    public function analyze(DeliveryPointInvoice $deliveryPointInvoice): void
    {
        $this->hasAnomaly = false;

        $consumption = $deliveryPointInvoice->getConsumption();

        if (!$quantity = $consumption->getQuantity()) {
            $this->ignore(transInfo('consumption_quantity_missing'), $this->getGroup().'.quantity');
            return;
        }

        try {
            $calculatedQuantity = $this->consumptionService->getConsumptionQuantity($consumption);
        } catch(IgnoreException $e) {
            $this->ignore($e->getTransMessage(), $e->getField());
            return;
        }

        if ($calculatedQuantity !== $quantity) {
            $anomalyDescription = transInfo('consumption_not_equal_to_indexes_difference', [
                'consumption' => $quantity,
                'finish_index' => $consumption->getIndexFinish(),
                'start_index' => $consumption->getIndexStart()
            ]);
            $this->anomaly(
                Anomaly::TYPE_CONSUMPTION,
                $anomalyDescription,
                $anomalyDescription,
                strval($quantity),
                null,
                transInfo('expected_value', ['expected_value' => strval($calculatedQuantity)]),
                $this->getGroup().'.quantity'
            );

            $this->hasAnomaly = true;
        }
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.consumption.quantity';
    }

    public function getGroup(): string
    {
        return AnalyzerInterface::GROUP_CONSUMPTION;
    }

    public function stopChain(): bool
    {
        return $this->hasAnomaly;
    }

    public function getPriority(): int
    {
        return 1;
    }
}