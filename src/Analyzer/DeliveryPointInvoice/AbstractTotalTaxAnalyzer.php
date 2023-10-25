<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice;

use App\Analyzer\AbstractAnalyzer;
use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceConsumption;
use App\Entity\Invoice\InvoiceTax;
use App\Entity\Pricing;
use App\Exceptions\IgnoreException;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Model\Turpe\TurpeModel;
use App\Model\Vat;
use App\Service\AmountConversionService;
use App\Service\LogService;
use App\Service\TurpeService;

abstract class AbstractTotalTaxAnalyzer extends AbstractAnalyzer
{
    abstract protected function getType(): string;
    abstract protected function getField(): string;
    abstract protected function getExpectedTotal(DeliveryPointInvoice $dpi): int;
    abstract protected function addTurpeTotal(TurpeModel $turpe): int;
    abstract protected function addTotal(int $amount, int $vat): int;

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
     * Checks the amount_ht|amount_tva|amount_ttc is correct
     * The total should be subscription + taxes + consumption (+ turpe if contract is "negotiated")
     */
    public function analyze(DeliveryPointInvoice $dpi): void
    {
        $consumption = $dpi->getConsumption();

        if (is_null($consumption->getIndexStartedAt())) {
            $this->ignore(transInfo('consumption_index_started_at_missing'), 'consumption.index_started_at');
            return;
        }

        if (is_null($consumption->getIndexFinishedAt())) {
            $this->ignore(transInfo('consumption_index_finished_at_missing'), 'consumption.index_finished_at');
            return;
        }

        if (is_null($consumption->getQuantity())) {
            $this->ignore(transInfo('consumption_quantity_missing'), 'consumption.quantity');
            return;
        }

        $calculatedTotal = 0;

        try {
            [$subscriptionTotal, $turpe] = $this->addSubscription($dpi);
            $calculatedTotal += $subscriptionTotal;
        } catch(IgnoreException $e) {
            $this->ignore($e->getTransMessage(), $e->getField());
            return;
        }

        $calculatedTotal += $this->addTaxes($dpi);
        $calculatedTotal += $this->addConsumption($consumption);

        $margin = 10**6; // 10c€ margin
        $totalMin = $calculatedTotal - $margin;
        $totalMax = $calculatedTotal + $margin;
        $expectedTotal = $this->getExpectedTotal($dpi);

        if ($expectedTotal < $totalMin || $expectedTotal > $totalMax) {
            $diff = $this->getAmountDiff($expectedTotal, $totalMin, $totalMax);
            $expectedTotal = $this->conversionService->intToHumanReadable($expectedTotal);
            $totalMinFormatted = $this->conversionService->intToHumanReadable($totalMin);
            $totalMaxFormatted = $this->conversionService->intToHumanReadable($totalMax);
            if ($this instanceof TotalTaxExcludedAnalyzer) {
                $appliedRules = $this->getAppliedRulesTaxExcluded($dpi, $totalMin, $totalMax, $margin, $turpe);
            } else {
                $appliedRules = $this->getAppliedRules($dpi, $totalMin, $totalMax, $margin, $turpe);
            }
            $this->anomaly(
                Anomaly::TYPE_AMOUNT,
                transInfo('amount_incorrect', ['amount_type' => strtoupper($this->getType()), 'type' => '']),
                $appliedRules,
                $expectedTotal,
                null,
                transInfo('expected_value_between_x_y', ['x' => $totalMinFormatted, 'y' => $totalMaxFormatted]),
                $this->getField(),
                $diff
            );
        }
    }

    private function addSubscription(DeliveryPointInvoice $dpi): array
    {
        // add subscription if contract is regulated, add TURPE otherwise
        $subscriptionTotal = 0;
        $subscription = $dpi->getSubscription();
        $contract = $dpi->getDeliveryPoint()->getContract();
        if ($contract && $contract->getType() === Pricing::TYPE_NEGOTIATED) {
            $consumption = $dpi->getConsumption();
            $period = $this->getMonthDiff($consumption->getIndexStartedAt(), $consumption->getIndexFinishedAt());
            if ($period === 0.0) {
                throw new IgnoreException(transInfo('consumption_period_must_be_at_least_one_month_long'), 'consumption');
            }

            // get the values of the Turpe during the interval provided
            [$turpeMin, $turpeMax] = $this->turpeService->getTurpeInterval($dpi->getPowerSubscribed(), $consumption->getIndexStartedAt(), $consumption->getIndexFinishedAt(), $consumption->getQuantity());

            // if the interval spans over several Turpe values, average min/max for each component
            $turpe = new TurpeModel(
                $turpeMin->getCg() !== $turpeMax->getCg() ? intval(round(($turpeMin->getCg() + $turpeMax->getCg()) / 2)) : $turpeMin->getCg(),
                $turpeMin->getCc() !== $turpeMax->getCc() ? intval(round(($turpeMin->getCc() + $turpeMax->getCc()) / 2)) : $turpeMin->getCc(),
                $turpeMin->getCsFixed() !== $turpeMax->getCsFixed() ? intval(round(($turpeMin->getCsFixed() + $turpeMax->getCsFixed()) / 2)) : $turpeMin->getCsFixed(),
                $turpeMin->getCsVariable() !== $turpeMax->getCsVariable() ? intval(round(($turpeMin->getCsVariable() + $turpeMax->getCsVariable()) / 2)) : $turpeMin->getCsVariable()
            );

            $subscriptionTotal += $this->addTurpeTotal($turpe);
        } else if ($subscription && $subscription->getTotal()) {
            $subscriptionTotal += $this->addTotal($subscription->getTotal(), Vat::REDUCED);
        }

        return [$subscriptionTotal, $turpe ?? null];
    }

    private function addConsumption(InvoiceConsumption $consumption): int
    {
        return $this->addTotal($consumption->getTotal() ?? 0, Vat::NORMAL);
    }

    private function addTaxes(DeliveryPointInvoice $dpi): int
    {
        $taxTotal = 0;
        foreach ($dpi->getTaxes() as $tax) {
            $taxTotal += $this->addTotal($tax->getTotal() ?? 0, $tax->getType() === InvoiceTax::TYPE_TAX_CTA ? Vat::REDUCED : Vat::NORMAL);
        }

        return $taxTotal;
    }

    /**
     * Gets the applied rules for TotalTaxAnalyzer and TotalTaxIncludedAnalyzer
     */
    protected function getAppliedRules(DeliveryPointInvoice $dpi, int $totalMin, int $totalMax, int $margin, ?TurpeModel $turpe): TranslationInfo
    {
        $type = $this->getType(); // 'ttc' or 'tva'
        $consumptionTotal = $dpi->getConsumption()->getTotal() ?? 0;
        $parameters = [
            'consumption_ht' => $this->conversionService->convertAndRound($consumptionTotal),
            "consumption_$type" => $this->conversionService->convertAndRound($this->addTotal($consumptionTotal, Vat::NORMAL)),
            "total_taxes_$type" => 0,
            'total_min' => $this->conversionService->convertAndRound($totalMin),
            'total_max' => $this->conversionService->convertAndRound($totalMax),
            'margin' => $this->conversionService->convertAndRound($margin),
            'tccfe_ht' => 0,
            'tdcfe_ht' => 0,
            'tcfe_ht' => 0,
            'cspe_ht' => 0,
            'cta_ht' => 0
        ];
        $taxes = $dpi->getTaxes();
        foreach ($taxes as $tax) {
            $taxTotal = $tax->getTotal() ?? 0;
            $taxTotalTtc = $this->addTotal($taxTotal, $tax->getType() === InvoiceTax::TYPE_TAX_CTA ? Vat::REDUCED : Vat::NORMAL);
            $parameters["{$tax->getType()}_ht"] = $this->conversionService->convertAndRound($taxTotal);
            $parameters["{$tax->getType()}_$type"] = $this->conversionService->convertAndRound($taxTotalTtc);
            $parameters["total_taxes_$type"] += $taxTotalTtc;
        }
        $parameters["total_taxes_$type"] = $this->conversionService->convertAndRound($parameters["total_taxes_$type"]);

        if ($turpe) {
            $translationKey = $type === 'ttc' ? 'total_tax_included_applied_rules_negotiated' : 'total_tax_applied_rules_negotiated';
            $parameters = array_merge($parameters, [
                'cg_ht' => $this->conversionService->convertAndRound($turpe->getCg()),
                "cg_$type" => $this->conversionService->convertAndRound($this->addTotal($turpe->getCg(), Vat::REDUCED)),
                'cc_ht' => $this->conversionService->convertAndRound($turpe->getCc()),
                "cc_$type" => $this->conversionService->convertAndRound($this->addTotal($turpe->getCc(), Vat::REDUCED)),
                'cs_fixed_ht' => $this->conversionService->convertAndRound($turpe->getCsFixed()),
                "cs_fixed_$type" => $this->conversionService->convertAndRound($this->addTotal($turpe->getCsFixed(), Vat::REDUCED)),
                'cs_variable_ht' => $this->conversionService->convertAndRound($turpe->getCsVariable()),
                "cs_variable_$type" => $this->conversionService->convertAndRound($this->addTotal($turpe->getCsVariable(), Vat::NORMAL)),
                "total_turpe_$type" => $this->conversionService->convertAndRound($this->addTurpeTotal($turpe))
            ]);
        } else {
            $translationKey = $type === 'ttc' ? 'total_tax_included_applied_rules_regulated' : 'total_tax_applied_rules_regulated';
            $subscriptionHt = $dpi->getSubscription()->getTotal() ?? 0;
            $parameters['subscription_ht'] = $this->conversionService->convertAndRound($subscriptionHt);
            $parameters["subscription_$type"] = $this->conversionService->convertAndRound($this->addTotal($subscriptionHt, Vat::REDUCED));
        }

        return transInfo($translationKey, $parameters);
    }

    public function getGroup(): string
    {
        return AnalyzerInterface::GROUP_DEFAULT;
    }
}