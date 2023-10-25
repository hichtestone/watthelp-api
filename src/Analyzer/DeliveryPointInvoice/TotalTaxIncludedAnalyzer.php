<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice;

use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceTax;
use App\Model\TranslationInfo;
use App\Model\Turpe\TurpeModel;
use App\Model\Vat;

class TotalTaxIncludedAnalyzer extends AbstractTotalTaxAnalyzer implements AnalyzerInterface
{
    protected function getType(): string
    {
        return 'ttc';
    }

    protected function getField(): string
    {
        return 'amount_ttc';
    }

    protected function getExpectedTotal(DeliveryPointInvoice $dpi): int
    {
        return $dpi->getAmountTTC();
    }

    protected function addTurpeTotal(TurpeModel $turpe): int
    {
        return $this->addTotal($turpe->getCg() + $turpe->getCc() + $turpe->getCsFixed(), Vat::REDUCED)
            + $this->addTotal($turpe->getCsVariable(), Vat::NORMAL);
    }

    protected function addTotal(int $amount, int $vat): int
    {
        return intval(round($amount * (1 + $vat/10000)));
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.total_tax_included';
    }

    public function getPriority(): int
    {
        return 2;
    }
}