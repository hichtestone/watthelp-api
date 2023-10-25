<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice;

use App\Analyzer\AbstractAnalyzer;
use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Manager\Invoice\DeliveryPointInvoiceManager;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Service\LogService;

class OneYearEstimatedAnalyzer extends AbstractAnalyzer implements AnalyzerInterface
{
    private DeliveryPointInvoiceManager $deliveryPointInvoiceManager;

    public function __construct(
        TranslationManager $translationManager,
        LogService $logger,
        DeliveryPointInvoiceManager $deliveryPointInvoiceManager
    ) {
        parent::__construct($translationManager, $logger);
        $this->deliveryPointInvoiceManager = $deliveryPointInvoiceManager;
    }

    /**
     * - Check if invoice exist at J - 365 and more => If not IGNORE this analyzer
     * - Get all "real" invoice during this 365 days if 0 we have an ANOMALY
     */
    public function analyze(DeliveryPointInvoice $deliveryPointInvoice): void
    {
        $deliveryPoint = $deliveryPointInvoice->getDeliveryPoint();
        $consumption = $deliveryPointInvoice->getConsumption();

        if (!$consumptionEnd = $consumption->getIndexFinishedAt()) {
            $this->ignore(
                transInfo('consumption_index_finished_at_missing'),
                'consumption.index_finished_at'
            );
            return;
        }

        $oneYear = (clone $consumptionEnd)->sub(new \DateInterval('P1Y'));

        // Check if delivery point invoice exist before J - 365
        $hasBefore = $this->deliveryPointInvoiceManager->hasBefore($deliveryPoint, $oneYear);
        if (!$hasBefore) {
            $this->ignore(transInfo('no_delivery_point_invoice_at_least_one_year_old'));
            return;
        }

        // Get all "real" invoice during this 365 days if 0 we have an anomaly
        $hasRealInvoice = $this->deliveryPointInvoiceManager->hasRealInvoiceBetweenInterval($deliveryPoint, $oneYear, $consumptionEnd);
        if (!$hasRealInvoice) {
            $this->anomaly(
                Anomaly::TYPE_DATE,
                transInfo('no_real_invoice_for_more_than_a_year')
            );
        }
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.one_year_estimated';
    }

    public function getGroup(): string
    {
        return AnalyzerInterface::GROUP_DEFAULT;
    }

    public function getPriority(): int
    {
        return 1;
    }
}