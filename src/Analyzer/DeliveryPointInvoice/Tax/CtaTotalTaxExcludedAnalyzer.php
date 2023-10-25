<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice\Tax;

use App\Analyzer\AbstractAnalyzer;
use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceTax;
use App\Exceptions\IgnoreException;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Model\Turpe\TurpeModel;
use App\Service\AmountConversionService;
use App\Service\ConsumptionService;
use App\Service\LogService;
use App\Service\TaxService;
use App\Service\TurpeService;

class CtaTotalTaxExcludedAnalyzer extends AbstractAnalyzer implements AnalyzerInterface
{
    private TurpeService $turpeService;
    private TaxService $taxService;
    private ConsumptionService $consumptionService;
    private AmountConversionService $conversionService;

    public function __construct(
        TranslationManager $translationManager,
        LogService $logger,
        TurpeService $turpeService,
        TaxService $taxService,
        ConsumptionService $consumptionService,
        AmountConversionService $conversionService
    ) {
        parent::__construct($translationManager, $logger);
        $this->turpeService = $turpeService;
        $this->taxService = $taxService;
        $this->consumptionService = $consumptionService;
        $this->conversionService = $conversionService;
    }

    /**
     * Checks the CTA amount is correct
     * It should be a percentage of the fixed part of the Turpe
     * The fixed part is: Composante de gestion (cg) + Composante de comptage (cc) + fixed part of Composante de soutirage (cs)
     * The percentage has been 27,04% for years but it can change
     */
    public function analyze(DeliveryPointInvoice $deliveryPointInvoice): void
    {
        $consumption = $deliveryPointInvoice->getConsumption();
        $taxes = $deliveryPointInvoice->getTaxes()->filter(fn (InvoiceTax $tax) => $tax->getType() === InvoiceTax::TYPE_TAX_CTA);
        if (!count($taxes)) {
            $this->ignore(transInfo('cta_missing'), $this->getGroup().'.'.InvoiceTax::TYPE_TAX_CTA);
            return;
        }

        $tax = $taxes->first();
        if (!$total = $tax->getTotal()) {
            $this->ignore(
                transInfo('ht_amount_missing', ['type' => 'tax', 'tax' => strtoupper(InvoiceTax::TYPE_TAX_CTA)]),
                $this->getGroup().'.'.InvoiceTax::TYPE_TAX_CTA.'.total'
            );
            return;
        }

        // get min/max values for the CTA and the Turpe for the interval provided
        try {
            ['cta'=> [$minCta, $maxCta]] = $this->taxService->getTaxAmounts($consumption, $deliveryPointInvoice->getDeliveryPoint()->getClient());

            $startedAt = $consumption->getIndexStartedAt();
            $finishedAt = $consumption->getIndexFinishedAt();

            /** @var TurpeModel $turpeMin */
            /** @var TurpeModel $turpeMax */
            [$turpeMin, $turpeMax] = $this->turpeService->getTurpeInterval($deliveryPointInvoice->getPowerSubscribed(), $startedAt, $finishedAt, $consumption->getQuantity());
        } catch(IgnoreException $e) {
            $this->ignore($e->getTransMessage(), $e->getField());
            return;
        }

        // cta is stored as an int, convert it back to its real floating point value
        $minCta /= 100;
        $maxCta /= 100;

        $margin = 10**5; // 1c€ margin
        $totalCtaMin = intval(round($turpeMin->getFixedTotal() * $minCta / 100) - $margin);
        $totalCtaMax = intval(round($turpeMax->getFixedTotal() * $maxCta / 100) + $margin);

        if ($total < $totalCtaMin || $total > $totalCtaMax) {
            $diff = $this->getAmountDiff($total, $totalCtaMin, $totalCtaMax);
            $totalFormatted = $this->conversionService->intToHumanReadable(intval($total));
            $totalCtaMinFormatted = $this->conversionService->intToHumanReadable(intval($totalCtaMin));
            $totalCtaMaxFormatted = $this->conversionService->intToHumanReadable(intval($totalCtaMax));
            $this->anomaly(
                Anomaly::TYPE_AMOUNT,
                transInfo('amount_incorrect', [
                    'amount_type' => 'HT',
                    'type' => 'tax',
                    'tax' => strtoupper(InvoiceTax::TYPE_TAX_CTA)
                ]),
                transInfo('cta_applied_rules', [
                    'from' => $consumption->getIndexStartedAt(),
                    'to' => $consumption->getIndexFinishedAt(),
                    'min_cta' => $minCta,
                    'max_cta' => $maxCta,
                    'consumption' => $consumption->getQuantity(),
                    'number_of_days' => $startedAt->diff($finishedAt)->days,
                    'min_cg' => $this->conversionService->convertAndRound($turpeMin->getCg()),
                    'max_cg' => $this->conversionService->convertAndRound($turpeMax->getCg()),
                    'min_cc' => $this->conversionService->convertAndRound($turpeMin->getCc()),
                    'max_cc' => $this->conversionService->convertAndRound($turpeMax->getCc()),
                    'min_fixed_cs' => $this->conversionService->convertAndRound($turpeMin->getCsFixed()),
                    'max_fixed_cs' => $this->conversionService->convertAndRound($turpeMax->getCsFixed()),
                    'min_turpe' => $this->conversionService->convertAndRound($turpeMin->getFixedTotal()),
                    'max_turpe' => $this->conversionService->convertAndRound($turpeMax->getFixedTotal()),
                    'margin' => $this->conversionService->convertAndRound($margin),
                    'total_min' => $this->conversionService->convertAndRound($totalCtaMin),
                    'total_max' => $this->conversionService->convertAndRound($totalCtaMax)
                ]),
                $totalFormatted,
                null,
                transInfo('expected_value_between_x_y', ['x' => $totalCtaMinFormatted, 'y' => $totalCtaMaxFormatted]),
                $this->getGroup().'.'.InvoiceTax::TYPE_TAX_CTA.'.total',
                $diff
            );
        }
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.tax.cta.total_tax_excluded';
    }

    public function getGroup(): string
    {
        return AnalyzerInterface::GROUP_TAX;
    }

    public function supportsAnalysis(DeliveryPointInvoice $deliveryPointInvoice): bool
    {
        $ctaTax = $deliveryPointInvoice->getTaxes()->filter(fn (InvoiceTax $tax) => $tax->getType() === InvoiceTax::TYPE_TAX_CTA);
        return !empty($ctaTax);
    }

    public function getPriority(): int
    {
        return 2;
    }
}