<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice;

use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Model\TranslationInfo;
use App\Model\Turpe\TurpeModel;
use App\Model\Vat;

class TotalTaxAnalyzer extends AbstractTotalTaxAnalyzer implements AnalyzerInterface
{
    protected function getType(): string
    {
        return 'tva';
    }

    protected function getField(): string
    {
        return 'amount_tva';
    }

    protected function getExpectedTotal(DeliveryPointInvoice $dpi): int
    {
        return $dpi->getAmountTVA();
    }

    protected function addTurpeTotal(TurpeModel $turpe): int
    {
        return $this->addTotal($turpe->getCg() + $turpe->getCc() + $turpe->getCsFixed(), Vat::REDUCED)
            + $this->addTotal($turpe->getCsVariable(), Vat::NORMAL);
    }

    protected function addTotal(int $amount, int $vat): int
    {
        return intval(round($amount * $vat/10000));
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.total_tax';
    }

    public function getPriority(): int
    {
        return 2;
    }
}