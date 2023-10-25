<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice\Subscription;

use App\Analyzer\AbstractAnalyzer;
use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Pricing;
use App\Manager\PricingManager;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Service\AmountConversionService;
use App\Service\DateFormatService;
use App\Service\LogService;

class TotalTaxExcludedAnalyzer extends AbstractAnalyzer implements AnalyzerInterface
{
    protected PricingManager $pricingManager;
    protected AmountConversionService $conversionService;

    public function __construct(
        TranslationManager $translationManager,
        LogService $logger,
        PricingManager $pricingManager,
        AmountConversionService $conversionService
    )
    {
        parent::__construct($translationManager, $logger);
        $this->pricingManager = $pricingManager;
        $this->conversionService = $conversionService;
    }

    /**
     * If the contract is "regulated", the subscription total should be subscriptionPrice * quantity
     * @throws \InvalidArgumentException
     */
    public function analyze(DeliveryPointInvoice $deliveryPointInvoice): void
    {
        $subscription = $deliveryPointInvoice->getSubscription();

        if (is_null($subscription->getTotal())) {
            $this->ignore(transInfo('ht_amount_missing', ['type' => 'subscription']), $this->getGroup().'.total');
            return;
        }

        if (is_null($subscription->getStartedAt())) {
            $this->ignore(transInfo('started_at_missing', ['type' => 'subscription']), $this->getGroup().'.started_at');
            return;
        }
        if (is_null($subscription->getFinishedAt())) {
            $this->ignore(transInfo('finished_at_missing', ['type' => 'subscription']), $this->getGroup().'.finished_at');
            return;
        }

        if (!$deliveryPointInvoice->getPowerSubscribed()) {
            $this->ignore(transInfo('power_subscribed_missing'), 'power_subscribed');
            return;
        }

        $pricings = $this->pricingManager->getPricingsBetweenInterval(
            $deliveryPointInvoice->getDeliveryPoint(),
            $subscription->getStartedAt(),
            $subscription->getFinishedAt()
        );

        if (count($pricings) === 0) {
            $this->ignore(transInfo('no_pricing_found_in_period', 
                [
                    'from' => $subscription->getStartedAt()->format(DateFormatService::ANALYZER),
                    'to'   => $subscription->getFinishedAt()->format(DateFormatService::ANALYZER)
                ]
            ));
            return;
        }

        $minPrice = null;
        $maxPrice = null;
        foreach($pricings as $pricing) {
            if (!$minPrice || $minPrice > $pricing->getSubscriptionPrice()) {
                $minPrice = $pricing->getSubscriptionPrice();
            }
            if (!$maxPrice || $maxPrice < $pricing->getSubscriptionPrice()) {
                $maxPrice = $pricing->getSubscriptionPrice();
            }
        }

        if ($deliveryPointInvoice->getDeliveryPoint()->getContract()->getInvoicePeriod()) {
            $period = $deliveryPointInvoice->getDeliveryPoint()->getContract()->getInvoicePeriod();
        } else {
            $period = $this->getMonthDiff($subscription->getStartedAt(), $subscription->getFinishedAt());
        }
        $period = intval($period);

        $powerSubscribed = floatval($deliveryPointInvoice->getPowerSubscribed());
        $margin = 10**5; // 1 c€
        $totalMin = intval(round($period * $minPrice * $powerSubscribed)) - $margin;
        $totalMax = intval(round($period * $maxPrice * $powerSubscribed)) + $margin;
        $total = $subscription->getTotal();

        if ($total < $totalMin || $total > $totalMax) {
            $diff = $this->getAmountDiff($total, $totalMin, $totalMax);
            $totalFormatted = $this->conversionService->intToHumanReadable($total);
            $totalMinFormatted = $this->conversionService->intToHumanReadable($totalMin);
            $totalMaxFormatted = $this->conversionService->intToHumanReadable($totalMax);
            $this->anomaly(
                Invoice\Anomaly::TYPE_AMOUNT,
                transInfo('amount_incorrect', ['amount_type' => 'HT', 'type' => 'subscription']),
                transInfo('subscription_total_tax_excluded_applied_rules', [
                    'from' => $subscription->getStartedAt(),
                    'to' => $subscription->getFinishedAt(),
                    'minimum_base_price' => $this->conversionService->convertAndRound($totalMin, 5),
                    'maximum_base_price' => $this->conversionService->convertAndRound($totalMax, 5),
                    'period' => $period,
                    'power_subscribed' => $powerSubscribed,
                    'margin' => $this->conversionService->convertAndRound($margin),
                    'minimum' => $this->conversionService->convertAndRound($totalMin),
                    'maximum' => $this->conversionService->convertAndRound($totalMax)
                ]),
                $totalFormatted,
                null,
                transInfo('expected_value_between_x_y', ['x' => $totalMinFormatted, 'y' => $totalMaxFormatted]),
                $this->getGroup().'.total',
                $diff
            );
        }
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.subscription.total_tax';
    }

    public function getGroup(): string
    {
        return AnalyzerInterface::GROUP_SUBSCRIPTION;
    }

    public function supportsAnalysis(DeliveryPointInvoice $deliveryPointInvoice): bool
    {
        return ($deliveryPointInvoice->getDeliveryPoint()->getContract() &&
                $deliveryPointInvoice->getDeliveryPoint()->getContract()->getType() === Pricing::TYPE_REGULATED);
    }

    public function getPriority(): int
    {
        return 1;
    }
}