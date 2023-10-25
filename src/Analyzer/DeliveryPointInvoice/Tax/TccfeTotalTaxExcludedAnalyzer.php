<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice\Tax;

use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\InvoiceTax;

class TccfeTotalTaxExcludedAnalyzer extends AbstractTotalTaxExcludedAnalyzer implements AnalyzerInterface
{
    protected function getInvoiceTaxType(): string
    {
        return InvoiceTax::TYPE_TAX_TCCFE;
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.tax.tccfe.total_tax_excluded';
    }
}