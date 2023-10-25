<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice\Consumption;

use App\Analyzer\AbstractAnalyzer;
use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Model\TranslationInfo;
use App\Service\DateFormatService;

class DateFinishedAtAnalyzer extends AbstractAnalyzer implements AnalyzerInterface
{

    public function analyze(DeliveryPointInvoice $deliveryPointInvoice): void
    {
        $consumption = $deliveryPointInvoice->getConsumption();

        $indexFinishedAt = $consumption->getIndexFinishedAt();
        $finishedAt = $consumption->getFinishedAt();
        if (!$indexFinishedAt || !$finishedAt) {
            if (!$indexFinishedAt) {
                $this->ignore(transInfo('consumption_index_finished_at_missing'), $this->getGroup().'.index_finished_at');
            }
            if (!$finishedAt) {
                $this->ignore(transInfo('finished_at_missing', ['type' => 'consumption']), $this->getGroup().'.finished_at');
            }
            return;
        }

        $period = $this->getDaysDiff($indexFinishedAt, $finishedAt);
        if ($period > 1) {
            $anomalyDescription = transInfo('index_finished_at_not_equal_to_finished_at', [
                'index_finished_at' => $indexFinishedAt,
                'finished_at' => $finishedAt
            ]);
            $this->anomaly(
                Anomaly::TYPE_DATE,
                $anomalyDescription,
                $anomalyDescription,
                $finishedAt->format(DateFormatService::ANALYZER),
                $indexFinishedAt->format(DateFormatService::ANALYZER),
                null,
                $this->getGroup().'.index_finished_at'
            );
        }
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.consumption.date_finished_at';
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