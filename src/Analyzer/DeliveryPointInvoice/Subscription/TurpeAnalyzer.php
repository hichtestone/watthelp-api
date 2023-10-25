<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice\Subscription;

use App\Analyzer\AbstractAnalyzer;
use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Pricing;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Service\AmountConversionService;
use App\Service\LogService;
use App\Service\TurpeService;

class TurpeAnalyzer extends AbstractAnalyzer implements AnalyzerInterface
{
    protected TurpeService $turpeService;
    protected AmountConversionService $conversionService;

    public function __construct(
        TranslationManager $translationManager,
        LogService $logger,
        TurpeService $turpeService,
        AmountConversionService $conversionService
    ) {
        $this->turpeService = $turpeService;
        $this->conversionService = $conversionService;
        parent::__construct($translationManager, $logger);
    }

    /**
     * If the contract is "negotiated", the subscription total should be equivalent to the Turpe
     */
    public function analyze(DeliveryPointInvoice $dpi): void
    {
        $subscription = $dpi->getSubscription();
        if (!$subscription) {
            $this->ignore(transInfo('subscription_missing'), $this->getGroup());
            return;
        }

        if (is_null($dpi->getPowerSubscribed())) {
            $this->ignore(transInfo('power_subscribed_missing'), 'power_subscribed');
            return;
        }

        if (!$subscription->getStartedAt()) {
            $this->ignore(transInfo('started_at_missing', ['type' => 'subscription']), 'subscription.started_at');
            return;
        }

        if (!$subscription->getFinishedAt()) {
            $this->ignore(transInfo('finished_at_missing', ['type' => 'subscription']), 'subscription.finished_at');
            return;
        }

        if (is_null($dpi->getConsumption()->getQuantity())) {
            $this->ignore(transInfo('consumption_quantity_missing'), 'consumption.quantity');
            return;
        }

        [$turpeMin, $turpeMax] = $this->turpeService->getTurpeInterval($dpi->getPowerSubscribed(), $subscription->getStartedAt(), $subscription->getFinishedAt(), $dpi->getConsumption()->getQuantity());

        $margin = 10**5; // 1 c€
        $totalTurpeMin = $turpeMin->getTotal() - $margin;
        $totalTurpeMax = $turpeMax->getTotal() + $margin;

        if ($subscription->getTotal() < $totalTurpeMin || $subscription->getTotal() > $totalTurpeMax) {

            // retrieve raw turpe data to display in the applied rules
            [
                'minAnnualCg' => $minAnnualCg, 'maxAnnualCg' => $maxAnnualCg,
                'minAnnualCc' => $minAnnualCc, 'maxAnnualCc' => $maxAnnualCc,
                'minCsCoeffPower' => $minCsCoeffPower, 'maxCsCoeffPower' => $maxCsCoeffPower,
                'minCsCoeffEnergy' => $minCsCoeffEnergy, 'maxCsCoeffEnergy' => $maxCsCoeffEnergy
            ] = $this->turpeService->getTurpeDataInterval($subscription->getStartedAt(), $subscription->getFinishedAt());

            $total = $this->conversionService->intToHumanReadable($subscription->getTotal());
            $totalTurpeMinFormatted = $this->conversionService->intToHumanReadable($totalTurpeMin);
            $totalTurpeMaxFormatted = $this->conversionService->intToHumanReadable($totalTurpeMax);
            $this->anomaly(
                Invoice\Anomaly::TYPE_TURPE,
                transInfo('amount_incorrect', ['amount_type' => '', 'type' => 'TURPE']),
                transInfo('turpe_applied_rules', [
                    'power_subscribed' => floatval($dpi->getPowerSubscribed()),
                    'consumption' => $dpi->getConsumption()->getQuantity(),
                    'subscription_days' => $subscription->getStartedAt()->diff($subscription->getFinishedAt())->days,
                    'from' => $subscription->getStartedAt(),
                    'to' => $subscription->getFinishedAt(),
                    'min_annual_cg' => $this->conversionService->convertAndRound($minAnnualCg),
                    'max_annual_cg' => $this->conversionService->convertAndRound($maxAnnualCg),
                    'min_cg' => $this->conversionService->convertAndRound($turpeMin->getCg()),
                    'max_cg' => $this->conversionService->convertAndRound($turpeMax->getCg()),
                    'min_annual_cc' => $this->conversionService->convertAndRound($minAnnualCc),
                    'max_annual_cc' => $this->conversionService->convertAndRound($maxAnnualCc),
                    'min_cc' => $this->conversionService->convertAndRound($turpeMin->getCc()),
                    'max_cc' => $this->conversionService->convertAndRound($turpeMax->getCc()),
                    'min_cs_coeff_power' => $this->conversionService->convertAndRound($minCsCoeffPower),
                    'max_cs_coeff_power' => $this->conversionService->convertAndRound($maxCsCoeffPower),
                    'min_cs_coeff_energy' => $this->conversionService->convertAndRound($minCsCoeffEnergy, 5),
                    'max_cs_coeff_energy' => $this->conversionService->convertAndRound($maxCsCoeffEnergy, 5),
                    'min_cs' => $this->conversionService->convertAndRound($turpeMin->getCs()),
                    'max_cs' => $this->conversionService->convertAndRound($turpeMax->getCs()),
                    'margin' => $this->conversionService->convertAndRound($margin),
                    'total_min' => $this->conversionService->convertAndRound($totalTurpeMin),
                    'total_max' => $this->conversionService->convertAndRound($totalTurpeMax)
                ]),
                $total,
                null,
                transInfo('expected_value_between_x_y', ['x' => $totalTurpeMinFormatted, 'y' => $totalTurpeMaxFormatted]),
                $this->getGroup().'.total'
            );
        }
    }

    public function supportsAnalysis(DeliveryPointInvoice $dpi): bool
    {
        return ($dpi->getDeliveryPoint()->getContract() &&
            $dpi->getDeliveryPoint()->getContract()->getType() === Pricing::TYPE_NEGOTIATED);
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.subscription.turpe';
    }

    public function getGroup(): string
    {
        return AnalyzerInterface::GROUP_SUBSCRIPTION;
    }

    public function getPriority(): int
    {
        return 2;
    }
}