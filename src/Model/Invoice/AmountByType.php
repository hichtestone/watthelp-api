<?php

declare(strict_types=1);

namespace App\Model\Invoice;

use App\Entity\Invoice;
use App\Entity\Invoice\InvoiceTax;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

class AmountByType
{
    /**
     * @Groups("default")
     * @SerializedName("subscription_cta")
     */
    private int $subscriptionAndCta;
    
    /**
     * @Groups("default")
     * @SerializedName("consumption_cspe_tcfe")
     */
    private int $consumptionCspeAndTcfe;

    public function __construct(Invoice $invoice)
    {
        $this->subscriptionAndCta = 0;
        $this->consumptionCspeAndTcfe = 0;
        foreach ($invoice->getDeliveryPointInvoices() as $dpi) {
            if ($dpi->getSubscription()) {
                $this->subscriptionAndCta += $dpi->getSubscription()->getTotal() ?? 0;
            }
            $this->consumptionCspeAndTcfe += $dpi->getConsumption()->getTotal() ?? 0;
            foreach ($dpi->getTaxes() as $tax) {
                switch ($tax->getType()) {
                    case InvoiceTax::TYPE_TAX_CTA:
                        $this->subscriptionAndCta += $tax->getTotal() ?? 0;
                        break;
                    default:
                        $this->consumptionCspeAndTcfe += $tax->getTotal() ?? 0;
                }
            }
        }
    }

    public function getSubscriptionAndCta(): int
    {
        return $this->subscriptionAndCta;
    }

    public function getConsumptionCspeAndTcfe(): int
    {
        return $this->consumptionCspeAndTcfe;
    }
}