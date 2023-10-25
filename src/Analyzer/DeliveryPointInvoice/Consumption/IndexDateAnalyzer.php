<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice\Consumption;

use App\Analyzer\AbstractAnalyzer;
use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Manager\Invoice\DeliveryPointInvoiceManager;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Service\DateFormatService;
use App\Service\LogService;

class IndexDateAnalyzer extends AbstractAnalyzer implements AnalyzerInterface
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

    public function analyze(DeliveryPointInvoice $dpi): void
    {
        $previous = $dpi->getDeliveryPointInvoiceAnalysis()->getPreviousDeliveryPointInvoice();

        if (!$previous) {
            $this->ignore(transInfo('no_previous_invoice_for_delivery_point'));
            return;
        }

        $consumption = $dpi->getConsumption();
        $previousConsumption = $previous->getConsumption();

        $indexStartedAt = $consumption->getIndexStartedAt();
        $indexFinishedAt = $previousConsumption->getIndexFinishedAt();

        if (!$indexStartedAt || !$indexFinishedAt) {
            if (!$indexStartedAt) {
                $this->ignore(transInfo('consumption_index_start_missing_in_current_invoice'), $this->getGroup().'.index_started_at');
            }
            if (!$indexFinishedAt) {
                $this->ignore(transInfo('consumption_index_finish_missing_in_previous_invoice'));
            }
            return;
        }

        $period = $this->getDaysDiff($indexFinishedAt, $indexStartedAt);
        if ($period > 2) {
            $anomalyDescription = transInfo('index_started_at_not_equal_to_previous_index_finished_at', [
                'index_started_at' => $indexStartedAt,
                'previous_index_finished_at' => $indexFinishedAt
            ]);
            $this->anomaly(
                Anomaly::TYPE_DATE,
                $anomalyDescription,
                $anomalyDescription,
                $indexStartedAt->format(DateFormatService::ANALYZER),
                $indexFinishedAt->format(DateFormatService::ANALYZER),
                transInfo('expected_value_give_or_take_two_days', ['expected_value' => $indexFinishedAt]),
                $this->getGroup().'.index_started_at',
                null,
                $previous
            );
        }
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.index_date';
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