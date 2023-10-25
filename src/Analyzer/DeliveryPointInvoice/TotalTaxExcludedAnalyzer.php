<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice;

use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Pricing;
use App\Model\TranslationInfo;
use App\Model\Turpe\TurpeModel;

class TotalTaxExcludedAnalyzer extends AbstractTotalTaxAnalyzer implements AnalyzerInterface
{
    protected function getType(): string
    {
        return 'ht';
    }

    protected function getField(): string
    {
        return 'amount_ht';
    }

    protected function getExpectedTotal(DeliveryPointInvoice $dpi): int
    {
        return $dpi->getAmountHT();
    }

    protected function addTurpeTotal(TurpeModel $turpe): int
    {
        return $turpe->getTotal();
    }

    protected function addTotal(int $amount, int $vat): int
    {
        return $amount;
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.total_tax_excluded';
    }

    public function getPriority(): int
    {
        return 2;
    }

    protected function getAppliedRulesTaxExcluded(DeliveryPointInvoice $dpi, int $totalMin, int $totalMax, int $margin, ?TurpeModel $turpe): TranslationInfo
    {
        $parameters = [
            'consumption' => $this->conversionService->convertAndRound($dpi->getConsumption()->getTotal() ?? 0),
            'total_taxes' => 0,
            'total_min' => $this->conversionService->convertAndRound($totalMin),
            'total_max' => $this->conversionService->convertAndRound($totalMax),
            'margin' => $this->conversionService->convertAndRound($margin),
            'tccfe' => 0,
            'tdcfe' => 0,
            'tcfe' => 0,
            'cspe' => 0,
            'cta' => 0,
        ];
        $taxes = $dpi->getTaxes();
        foreach ($taxes as $tax) {
            $taxTotal = $tax->getTotal() ?? 0;
            $parameters[$tax->getType()] = $this->conversionService->convertAndRound($taxTotal);
            $parameters['total_taxes'] += $taxTotal;
        }
        $parameters['total_taxes'] = $this->conversionService->convertAndRound($parameters['total_taxes']);

        if ($turpe) {
            $translationKey = 'total_tax_excluded_applied_rules_negotiated';
            $parameters = array_merge($parameters, [
                'cg' => $this->conversionService->convertAndRound($turpe->getCg()),
                'cc' => $this->conversionService->convertAndRound($turpe->getCc()),
                'cs' => $this->conversionService->convertAndRound($turpe->getCs()),
                'total_turpe' => $this->conversionService->convertAndRound($turpe->getTotal())
            ]);
        } else {
            $translationKey = 'total_tax_excluded_applied_rules_regulated';
            $parameters['subscription'] = $this->conversionService->convertAndRound($dpi->getSubscription()->getTotal() ?? 0);
        }

        return transInfo($translationKey, $parameters);
    }
}