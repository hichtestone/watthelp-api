<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice\Consumption;

use App\Analyzer\AbstractAnalyzer;
use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Manager\PricingManager;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Service\AmountConversionService;
use App\Service\DateFormatService;
use App\Service\LogService;

class UnitPriceAnalyzer extends AbstractAnalyzer implements AnalyzerInterface
{
    private PricingManager $pricingManager;
    private AmountConversionService $conversionService;

    public function __construct(
        TranslationManager $translationManager,
        LogService $logger,
        PricingManager $pricingManager,
        AmountConversionService $conversionService
    ) {
        parent::__construct($translationManager, $logger);
        $this->pricingManager = $pricingManager;
        $this->conversionService = $conversionService;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function analyze(DeliveryPointInvoice $deliveryPointInvoice): void
    {
        $consumption = $deliveryPointInvoice->getConsumption();
        if (is_null($consumption->getUnitPrice())) {
            $this->ignore(transInfo('unit_price_missing'), $this->getGroup().'.unit_price');
            return;
        }
        $unitPrice = $consumption->getUnitPrice();

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

        if ($unitPrice < $min || $unitPrice > $max) {
            $unitPrice = $this->conversionService->intToHumanReadableInCents($unitPrice, 3);
            $minFormatted = $this->conversionService->intToHumanReadableInCents($min, 3);
            $maxFormatted = $this->conversionService->intToHumanReadableInCents($max, 3);
            $this->anomaly(
                Anomaly::TYPE_CONSUMPTION,
                transInfo('unit_price_incorrect'),
                transInfo('consumption_unit_price_applied_rules', [
                    'from' => $consumption->getIndexStartedAt(),
                    'to' => $consumption->getIndexFinishedAt(),
                    'minimum_base_price' => $this->conversionService->convertAndRound($min, 5),
                    'maximum_base_price' => $this->conversionService->convertAndRound($max, 5)
                ]),
                $unitPrice,
                null,
                transInfo('expected_value_between_x_y', ['x' => $minFormatted, 'y' => $maxFormatted]),
                $this->getGroup().'.unit_price'
            );
        }
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.consumption.unit_price';
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