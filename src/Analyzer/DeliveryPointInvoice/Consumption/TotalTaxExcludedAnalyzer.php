<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice\Consumption;

use App\Analyzer\AbstractAnalyzer;
use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Exceptions\IgnoreException;
use App\Manager\PricingManager;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Service\AmountConversionService;
use App\Service\ConsumptionService;
use App\Service\DateFormatService;
use App\Service\LogService;

class TotalTaxExcludedAnalyzer extends AbstractAnalyzer implements AnalyzerInterface
{
    private ConsumptionService $consumptionService;
    private PricingManager $pricingManager;
    private AmountConversionService $conversionService;

    public function __construct(
        TranslationManager $translationManager,
        LogService $logger,
        ConsumptionService $consumptionService,
        PricingManager $pricingManager,
        AmountConversionService $conversionService
    ) {
        parent::__construct($translationManager, $logger);
        $this->consumptionService = $consumptionService;
        $this->pricingManager = $pricingManager;
        $this->conversionService = $conversionService;
    }

    public function analyze(DeliveryPointInvoice $deliveryPointInvoice): void
    {
        $consumption = $deliveryPointInvoice->getConsumption();

        if(!$total = $consumption->getTotal()) {
            $this->ignore(transInfo('ht_amount_missing'), $this->getGroup().'.total');
            return;
        }

        $pricings = $this->pricingManager->getPricingsBetweenInterval(
            $deliveryPointInvoice->getDeliveryPoint(),
            $consumption->getIndexStartedAt(),
            $consumption->getIndexFinishedAt()
        );

        if (count($pricings) === 0) {
            $this->ignore(transInfo('no_pricing_found_in_period', 
                [
                    'from' => $consumption->getIndexStartedAt()->format(DateFormatService::ANALYZER),
                    'to'   => $consumption->getIndexFinishedAt()->format(DateFormatService::ANALYZER)
                ]
            ));
            return;
        }

        $min = null;
        $max = null;
        foreach($pricings as $pricing) {
            if (!$min || $min > $pricing->getConsumptionBasePrice()) {
                $min = $pricing->getConsumptionBasePrice();
            }
            if (!$max || $max < $pricing->getConsumptionBasePrice()) {
                $max = $pricing->getConsumptionBasePrice();
            }
        }

        try {
            $calculatedQuantity = $this->consumptionService->getConsumptionQuantity($consumption);
        } catch (IgnoreException $e) {
            $this->ignore($e->getTransMessage(), $e->getField());
            return;
        }

        // +/- 10c€ is marge tolerance because round on imported file are false.
        $margin = 10**6;
        $totalMin = ($min * $calculatedQuantity) - $margin;
        $totalMax = ($max * $calculatedQuantity) + $margin;

        if ($total < $totalMin || $total > $totalMax) {
            $diff = $this->getAmountDiff($total, $totalMin, $totalMax);
            $totalFormatted = $this->conversionService->intToHumanReadable($total);
            $totalMinFormatted = $this->conversionService->intToHumanReadable($totalMin);
            $totalMaxFormatted = $this->conversionService->intToHumanReadable($totalMax);
            $this->anomaly(
                Anomaly::TYPE_CONSUMPTION,
                transInfo('amount_incorrect', ['amount_type' => 'HT', 'type' => 'consumption']),
                transInfo('consumption_total_tax_excluded_applied_rules', [
                    'from' => $consumption->getIndexStartedAt(),
                    'to' => $consumption->getIndexFinishedAt(),
                    'minimum_base_price' => $this->conversionService->convertAndRound($min, 5),
                    'maximum_base_price' => $this->conversionService->convertAndRound($max, 5),
                    'finish_index' => $consumption->getIndexFinish() ?? 0,
                    'start_index' => $consumption->getIndexStart() ?? 0,
                    'consumption' => $calculatedQuantity,
                    'margin' => $this->conversionService->convertAndRound($margin),
                    'minimum' => $this->conversionService->convertAndRound($totalMin),
                    'maximum' => $this->conversionService->convertAndRound($totalMax)
                ]),
                strval($totalFormatted),
                null,
                transInfo('expected_value_between_x_y', ['x' => $totalMinFormatted, 'y' => $totalMaxFormatted]),
                $this->getGroup().'.total',
                $diff
            );
        }
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.consumption.total_tax_excluded';
    }

    public function getGroup(): string
    {
        return AnalyzerInterface::GROUP_CONSUMPTION;
    }

    public function getPriority(): int
    {
        return 2;
    }
}