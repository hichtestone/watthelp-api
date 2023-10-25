<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice\Consumption;

use App\Analyzer\AbstractAnalyzer;
use App\Analyzer\AnalyzerInterface;
use App\Entity\Building;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Manager\Invoice\DeliveryPointInvoiceManager;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Service\LogService;

class OneYearDiffAnalyzer extends AbstractAnalyzer implements AnalyzerInterface
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
     * Checks the consumption of a given delivery point hasn't changed more than 5% compared to last year
     */
    public function analyze(DeliveryPointInvoice $deliveryPointInvoice): void
    {
        $deliveryPoint = $deliveryPointInvoice->getDeliveryPoint();
        $diff = 5;

        $consumption = $deliveryPointInvoice->getConsumption();

        $consumptionEnd = $consumption->getIndexFinishedAt();
        if (!$consumptionEnd) {
            $this->ignore(transInfo('consumption_index_finished_at_missing'), $this->getGroup().'.index_finished_at');
            return;
        }
        
        $oneYear = (clone $consumptionEnd)->sub(new \DateInterval('P1Y'));
        $twoYear = (clone $consumptionEnd)->sub(new \DateInterval('P2Y'));

        // We need 2 year of data to be able to analyze
        $has2YearOfData = $this->deliveryPointInvoiceManager->hasBefore($deliveryPoint, $twoYear);
        if (!$has2YearOfData) {
            $this->ignore(transInfo('two_years_are_required'), $this->getGroup());
            return;
        }

        $consumptionYear2 = $this->deliveryPointInvoiceManager->getSumConsumptionBetweenInterval($deliveryPoint, $twoYear, $oneYear);
        if (!$consumptionYear2) {
            $this->ignore(transInfo('no_consumption_in_year', ['year' => 'previous']), $this->getGroup());
            return;
        }

        $consumptionYear = $this->deliveryPointInvoiceManager->getSumConsumptionBetweenInterval($deliveryPoint, $oneYear, $consumptionEnd);
        if (!$consumptionYear) {
            $this->ignore(transInfo('no_consumption_in_year', ['year' => 'current']), $this->getGroup());
            return;
        }

        $evolution = abs(round(($consumptionYear2 - $consumptionYear) / $consumptionYear2 * 100));
        if ($evolution > $diff) {
            $this->anomaly(
                Anomaly::TYPE_CONSUMPTION,
                transInfo('consumption_changed_more_than_x_percent', ['percentage' => $diff]),
                transInfo('one_year_diff_applied_rules', [
                    'two_years_beforehand' => $twoYear,
                    'one_year_beforehand' => $oneYear,
                    'consumption_year_before_last' => $consumptionYear2,
                    'percentage' => $evolution,
                    'consumption_finished_at' => $consumptionEnd,
                    'consumption_last_year' => $consumptionYear
                ]),
                strval($consumptionYear),
                strval($consumptionYear2),
                null,
                $this->getGroup().'.quantity'
            );
        }
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.consumption.one_year_diff';
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