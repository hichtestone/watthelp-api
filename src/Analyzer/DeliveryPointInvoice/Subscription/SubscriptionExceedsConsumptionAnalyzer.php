<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice\Subscription;

use App\Analyzer\AbstractAnalyzer;
use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Manager\TranslationManager;
use App\Model\AmountDiff;
use App\Model\TranslationInfo;
use App\Service\AmountConversionService;
use App\Service\LogService;

class SubscriptionExceedsConsumptionAnalyzer extends AbstractAnalyzer implements AnalyzerInterface
{
    private AmountConversionService $conversionService;

    public function __construct(
        TranslationManager $translationManager,
        LogService $logger,
        AmountConversionService $conversionService
    ) {
        parent::__construct($translationManager, $logger);
        $this->conversionService = $conversionService;
    }

    public function analyze(DeliveryPointInvoice $deliveryPointInvoice): void
    {
        $subscriptionTotal = $deliveryPointInvoice->getSubscription()->getTotal();
        $consumptionTotal = $deliveryPointInvoice->getConsumption()->getTotal();

        if (is_null($subscriptionTotal)) {
            $this->ignore(transInfo('ht_amount_missing', ['type' => 'subscription']), $this->getGroup().'.total');
            return;
        }

        if (is_null($consumptionTotal)) {
            $this->ignore(transInfo('ht_amount_missing', ['type' => 'consumption']), 'consumption.total');
            return;            
        }

        if ($subscriptionTotal > $consumptionTotal) {
            $diffAmount = $subscriptionTotal - $consumptionTotal;
            $diffAmountPercentage = round((($subscriptionTotal-$consumptionTotal)/$consumptionTotal)*100, 2);
            $subscriptionTotal = $this->conversionService->intToHumanReadable($subscriptionTotal);
            $consumptionTotal = $this->conversionService->intToHumanReadable($consumptionTotal);
            $anomalyDescription = transInfo(
                'subscription_amount_exceeds_consumption_amount',
                [
                    'subscription_amount' => $subscriptionTotal,
                    'consumption_total' => $consumptionTotal
                ]
            );
            $this->anomaly(
                Anomaly::TYPE_AMOUNT,
                $anomalyDescription,
                $anomalyDescription,
                strval($subscriptionTotal),
                null,
                transInfo('expected_value_inferior_to', ['expected_value' => $consumptionTotal]),
                $this->getGroup().'.total',
                new AmountDiff($diffAmount, $diffAmountPercentage)
            );
        }
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.subscription.subscription_exceeds_consumption';
    }

    public function supportsAnalysis(DeliveryPointInvoice $dpi): bool
    {
        return !is_null($dpi->getSubscription());
    }

    public function getGroup(): string
    {
        return AnalyzerInterface::GROUP_SUBSCRIPTION;
    }
}