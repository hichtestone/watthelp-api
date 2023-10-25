<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice\Consumption;

use App\Analyzer\AbstractAnalyzer;
use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;

class IndexAnalyzer extends AbstractAnalyzer implements AnalyzerInterface
{
    public function analyze(DeliveryPointInvoice $dpi): void
    {
        $previous = $dpi->getDeliveryPointInvoiceAnalysis()->getPreviousDeliveryPointInvoice();

        if (!$previous) {
            $this->ignore(transInfo('no_previous_invoice_for_delivery_point'));
            return;
        }

        $consumption = $dpi->getConsumption();
        $previousConsumption = $previous->getConsumption();

        $indexStart = $consumption->getIndexStart();
        $indexFinish = $previousConsumption->getIndexFinish();
        if (is_null($indexStart) || is_null($indexFinish)) {
            if (is_null($indexStart)) {
                $this->ignore(transInfo('consumption_index_start_missing_in_current_invoice'), $this->getGroup().'.index_start');
            }
            if (is_null($indexFinish)) {
                $this->ignore(transInfo('consumption_index_finish_missing_in_previous_invoice'));
            }
            return;
        }

        if ($consumption->getIndexStart() !== $previousConsumption->getIndexFinish()) {
            $this->anomaly(
                Anomaly::TYPE_INDEX,
                transInfo('index_start_not_equal_to_previous_index_finish'),
                sprintf('%s != %s', $indexStart, $indexFinish),
                strval($indexStart),
                strval($indexFinish),
                transInfo('expected_value', ['expected_value' => strval($indexFinish)]),
                $this->getGroup().'.index_start',
                null,
                $previous
            );
        }
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.index';
    }

    public function getGroup(): string
    {
        return AnalyzerInterface::GROUP_CONSUMPTION;
    }

    public function getPriority(): int
    {
        return 1;
    }
}