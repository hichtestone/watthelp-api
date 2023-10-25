<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice\Consumption;

use App\Analyzer\AbstractAnalyzer;
use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Model\TranslationInfo;
use App\Service\DateFormatService;

class DateStartedAtAnalyzer extends AbstractAnalyzer implements AnalyzerInterface
{

    public function analyze(DeliveryPointInvoice $deliveryPointInvoice): void
    {
        $consumption = $deliveryPointInvoice->getConsumption();

        $indexStartedAt = $consumption->getIndexStartedAt();
        $startedAt = $consumption->getStartedAt();
        if (!$indexStartedAt || !$startedAt) {
            if (!$indexStartedAt) {
                $this->ignore(transInfo('consumption_index_started_at_missing'), $this->getGroup().'.index_started_at');
            }
            if (!$startedAt) {
                $this->ignore(transInfo('started_at_missing', ['type' => 'consumption']), $this->getGroup().'.started_at');
            }
            return;
        }

        $period = $this->getDaysDiff($indexStartedAt, $startedAt);
        if ($period > 1) {
            $anomalyDescription = transInfo('index_started_at_not_equal_to_started_at', [
                'index_started_at' => $indexStartedAt,
                'started_at' => $startedAt
            ]);
            $this->anomaly(
                Anomaly::TYPE_DATE,
                $anomalyDescription,
                $anomalyDescription,
                $startedAt->format(DateFormatService::ANALYZER),
                $indexStartedAt->format(DateFormatService::ANALYZER),
                null,
                $this->getGroup().'.index_started_at'
            );
        }
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.consumption.date_started_at';
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