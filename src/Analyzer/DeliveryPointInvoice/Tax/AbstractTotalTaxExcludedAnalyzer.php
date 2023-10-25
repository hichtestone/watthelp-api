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
use App\Service\AmountConversionService;
use App\Service\ConsumptionService;
use App\Service\LogService;
use App\Service\TaxService;

abstract class AbstractTotalTaxExcludedAnalyzer extends AbstractAnalyzer
{
    abstract protected function getInvoiceTaxType();

    protected TaxService $taxService;
    protected ConsumptionService $consumptionService;
    protected AmountConversionService $conversionService;

    public function __construct(
        TranslationManager $translationManager,
        LogService $logger,
        TaxService $taxService,
        ConsumptionService $consumptionService,
        AmountConversionService $conversionService
    ) {
        parent::__construct($translationManager, $logger);
        $this->taxService = $taxService;
        $this->consumptionService = $consumptionService;
        $this->conversionService = $conversionService;
    }

    /**
     * Checks the CSPE, TDCFE, TCCFE and TCFE taxes
     * For CSPE, TDCFE and TCCFE the value should be the tax unit price * quantity used
     * For TCFE it should be (TDCFE+TCCFE) unit prices * quantity used
     */
    public function analyze(DeliveryPointInvoice $deliveryPointInvoice): void
    {
        $deliveryPoint = $deliveryPointInvoice->getDeliveryPoint();
        $type = $this->getInvoiceTaxType();

        $tax = $deliveryPointInvoice->getTaxes()->filter(fn (InvoiceTax $tax) => $tax->getType() === $type)->first();
        $total = $tax->getTotal();
        if (is_null($total)) {
            $this->ignore(
                transInfo('ht_amount_missing', ['type' => 'tax', 'tax' => strtoupper($type)]),
                $this->getGroup().'.'.$type.'.total'
            );
            return;
        }

        $client = $deliveryPoint->getClient();
        $consumption = $deliveryPointInvoice->getConsumption();
        try {
            $consumptionQuantity = $this->consumptionService->getConsumptionQuantity($consumption);
            if ($type === InvoiceTax::TYPE_TAX_TCFE) {
                [
                    'tdcfe' => [$minTdcfe, $maxTdcfe],
                    'tccfe' => [$minTccfe, $maxTccfe]
                ] = $this->taxService->getTaxAmounts($consumption, $client);
                $min = !is_null($minTdcfe) && !is_null($minTccfe) ? $minTdcfe + $minTccfe : null;
                $max = !is_null($maxTdcfe) && !is_null($maxTccfe) ? $maxTdcfe + $maxTccfe : null;
            } else {
                [
                    $type => [$min, $max],
                ] = $this->taxService->getTaxAmounts($consumption, $client);
            }
            if (is_null($min) || is_null($max)) {
                $this->ignore(transInfo('could_not_get_min_max_tax'));
                return;
            }
        } catch(IgnoreException $e) {
            $this->ignore($e->getTransMessage(), $e->getField());
            return;
        }

        $margin = 10**5; // 1 c€
        $totalMin = ($min * $consumptionQuantity) - $margin;
        $totalMax = ($max * $consumptionQuantity) + $margin;

        if (!is_null($total) && ($total < $totalMin || $total > $totalMax)) {
            $diff = $this->getAmountDiff($total, $totalMin, $totalMax);
            $total = $this->conversionService->intToHumanReadable($total);
            $totalMinFormatted = $this->conversionService->intToHumanReadable($totalMin);
            $totalMaxFormatted = $this->conversionService->intToHumanReadable($totalMax);
            $appliedRulesCommonParameters = [
                'tax' => strtoupper($type),
                'from' => $consumption->getIndexStartedAt(),
                'to' => $consumption->getIndexFinishedAt(),
                'consumption' => $consumptionQuantity,
                'margin' => $this->conversionService->convertAndRound($margin),
                'total_min' => $this->conversionService->convertAndRound($totalMin),
                'total_max' => $this->conversionService->convertAndRound($totalMax)
            ];
            if ($type === InvoiceTax::TYPE_TAX_TCFE) {
                $appliedRules = transInfo('tcfe_applied_rules', array_merge($appliedRulesCommonParameters, [
                    'min_tdcfe_unit_price' => $this->conversionService->convertInCentsAndRound($minTdcfe, 5),
                    'max_tdcfe_unit_price' => $this->conversionService->convertInCentsAndRound($maxTdcfe, 5),
                    'min_tccfe_unit_price' => $this->conversionService->convertInCentsAndRound($minTccfe, 5),
                    'max_tccfe_unit_price' => $this->conversionService->convertInCentsAndRound($maxTccfe, 5),
                ]));
            } else {
                $appliedRules = transInfo('tax_applied_rules', array_merge($appliedRulesCommonParameters, [
                    'min_tax_unit_price' => $this->conversionService->convertInCentsAndRound($min, 5),
                    'max_tax_unit_price' => $this->conversionService->convertInCentsAndRound($max, 5),
                ]));
            }
            $this->anomaly(
                Anomaly::TYPE_AMOUNT,
                transInfo('amount_incorrect', [
                    'amount_type' => 'HT',
                    'type' => 'tax',
                    'tax' => strtoupper($type)
                ]),
                $appliedRules,
                strval($total),
                null,
                transInfo('expected_value_between_x_y', ['x' => $totalMinFormatted, 'y' => $totalMaxFormatted]),
                $this->getGroup().'.'.$type.'.total',
                $diff
            );
        }
    }

    public function getGroup(): string
    {
        return AnalyzerInterface::GROUP_TAX;
    }

    public function getPriority(): int
    {
        return 2;
    }

    public function supportsAnalysis(DeliveryPointInvoice $deliveryPointInvoice): bool
    {
        return !$deliveryPointInvoice->getTaxes()->filter(fn (InvoiceTax $tax) => $tax->getType() === $this->getInvoiceTaxType())->isEmpty();
    }
}