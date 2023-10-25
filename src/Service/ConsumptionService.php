<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Client;
use App\Entity\Invoice\InvoiceConsumption;
use App\Exceptions\IgnoreException;
use App\Factory\PeriodFactory;
use App\Manager\Invoice\DeliveryPointInvoiceManager;
use App\Manager\Invoice\InvoiceConsumptionManager;
use App\Manager\Invoice\InvoiceTaxManager;
use App\Model\Period;
use App\Model\TranslationInfo;
use App\Model\Year;
use App\Service\ConsumptionService\Context;

class ConsumptionService
{
    use ConsumptionCalculationTrait;

    private InvoiceConsumptionManager $invoiceConsumptionManager;
    private DeliveryPointInvoiceManager $dpiManager;
    private InvoiceTaxManager $invoiceTaxManager;

    public function __construct(
        InvoiceConsumptionManager $invoiceConsumptionManager,
        DeliveryPointInvoiceManager $dpiManager,
        InvoiceTaxManager $invoiceTaxManager
    )
    {
        $this->invoiceConsumptionManager = $invoiceConsumptionManager;
        $this->dpiManager = $dpiManager;
        $this->invoiceTaxManager = $invoiceTaxManager;
    }

    /**
     * Number of hours consumed for each month for the public light
     */
    public const HOURS_BY_MONTH = [
        Year::JANUARY => 445,
        Year::FEBRUARY => 370,
        Year::MARCH => 365,
        Year::APRIL => 305,
        Year::MAY => 270,
        Year::JUNE => 240,
        Year::JULY => 260,
        Year::AUGUST => 300,
        Year::SEPTEMBER => 335,
        Year::OCTOBER => 396,
        Year::NOVEMBER => 427,
        Year::DECEMBER => 467
    ];

    /**
     * @throws IgnoreException
     */
    public function getConsumptionQuantity(InvoiceConsumption $consumption): int
    {
        $indexFinish = $consumption->getIndexFinish();
        $indexStart = $consumption->getIndexStart();
        $quantity = $consumption->getQuantity();

        if (!is_null($indexFinish) && !is_null($indexStart) && !is_null($quantity)) {
            // Corner case: edf meter stop at 99999 and restart to 0. So we can have an index finish smaller than index start
            if ($indexFinish < $indexStart && $quantity > 0) {
                $calculatedQuantity = (100000 + $indexFinish) - $indexStart;
            } else {
                $calculatedQuantity = $indexFinish - $indexStart;
            }
        } elseif (!is_null($quantity)) {
            $calculatedQuantity = $consumption->getQuantity();
        } else {

            if (is_null($indexFinish)) {
                throw new IgnoreException(transInfo('index_finish_missing'), 'consumption.index_finish');
            }

            if (is_null($indexStart)) {
                throw new IgnoreException(transInfo('index_start_missing'), 'consumption.index_start');
            }

            if (is_null($quantity)) {
                throw new IgnoreException(transInfo('consumption_quantity_missing'), 'consumption.quantity');
            }
        }

        return $calculatedQuantity;
    }

    public function getTotalConsumptionQuantityByMonths(array $consumptions, int $year, ?Period $period = null): Year
    {
        $period ??= PeriodFactory::createFromYear($year);
        $totalConsumptionsByMonth = new Year(null);

        foreach ($consumptions as $consumption) {
            $consumptionByMonth = $this->getConsumptionQuantityByMonth($consumption, $period);
            foreach ($consumptionByMonth as $month => $monthConsumption) {
                if ($monthConsumption) {
                    $totalConsumptionsByMonth->setMonthValue(
                        $month,
                        $totalConsumptionsByMonth->getMonthValue($month) + $monthConsumption
                    );                    
                }
            }
        }

        return $totalConsumptionsByMonth;
    }

    /**
     * Returns calculated_quantity and calculated_amount of the consumptions for a given year
     */
    public function getTotalCalculatedConsumptionsByYear(array $consumptions, int $year): array
    {
        $totalCalculatedQuantity = $totalCalculatedAmount = 0;

        foreach ($consumptions as $consumption) {

            if (!$consumption->getStartedAt() || !$consumption->getFinishedAt() || !$consumption->getQuantity() || !$consumption->getTotal()) {
                continue;
            }

            $start  = intval($consumption->getStartedAt()->format('Y'));
            $finish = intval($consumption->getFinishedAt()->format('Y'));

            if ($start === $year && $finish === $year) {
                $totalCalculatedQuantity += $consumption->getQuantity();
                $totalCalculatedAmount   += $consumption->getTotal();
            } else {
                // the consumption spans over 2 years, we need to calculate the quantity/amount relative to
                // how much was consumed in the year provided in parameter
                $consumptionByMonth = $this->getConsumptionQuantityByMonthOfYear($consumption, $year);
            
                $calculatedQuantity = array_sum(array_values($consumptionByMonth->getMonths()));
                if ($calculatedQuantity === 0) {
                    continue;
                }
                $calculatedAmount = intval(round(($calculatedQuantity / $consumption->getQuantity()) * $consumption->getTotal()));

                $totalCalculatedQuantity += $calculatedQuantity;
                $totalCalculatedAmount   += $calculatedAmount;
            }
        }

        return [
            'calculated_quantity' => $totalCalculatedQuantity,
            'calculated_amount'   => $totalCalculatedAmount
        ];
    }

    public function getConsumptionQuantityByMonthOfYear(InvoiceConsumption $consumption, int $year): Year
    {
        return $this->getConsumptionQuantityByMonth($consumption, PeriodFactory::createFromYear($year));
    }

    /**
     * Spreads the consumption over months, i.e. if the consumption is from 21/03 to 21/05 spread
     * the quantity over March, April and May
     * It takes into account how many days were consumed in that month and how many hours a given
     * month can have because for example the public light is used more in winter than in summer
     *
     * If the consumption spans over 2 years, e.g. from 15/11/2019 to 15/11/2020, only the relative
     * consumption of the months in the year given in parameter will be returned
     * In this case, if year is 2019, only consumptions from November and December will be returned
     * Otherwise if year is 2020, only January and February will be returned
     * @throws \LogicException
     */
    public function getConsumptionQuantityByMonth(InvoiceConsumption $consumption, Period $period): Year
    {
        $periodStart = $period->getStart();
        $periodEnd   = $period->getEnd();
        
        if ($periodStart->format('Y') !== $periodEnd->format('Y')) {
            throw new \LogicException('La période ne doit pas chevaucher deux années.');
        }
        if ($periodStart->format('n') >= $periodEnd->format('n') ||
            $periodEnd->diff($periodStart)->format('%a') < 30) {
            throw new \LogicException('La période doit être d\'au moins un mois.');
        }
        $periodYear = intval($periodStart->format('Y'));
        $periodStartMonth = intval($periodStart->format('n'));
        $periodStartDay = intval($periodStart->format('j'));
        $periodEndMonth = intval($periodEnd->format('n'));
        $periodEndDay = intval($periodEnd->format('j'));

        $hoursByMonth = $consumptionByMonth = new Year(null);

        $totalHours = 0;
        $current = clone $consumption->getStartedAt();
        $finish = clone $consumption->getFinishedAt();
        $quantity = $consumption->getQuantity();

        if (!$current || !$finish || !$quantity) {
            return $consumptionByMonth;
        }

        $finishMonth = intval($finish->format('n'));
        $finishYear = intval($finish->format('Y'));
        while ($current < $finish) {
            $currentYear = intval($current->format('Y'));
            $currentMonth = intval($current->format('n'));
            $firstDayOfMonthConsumption = intval($current->format('j'));

            $ratio = 1; // between 0 and 1, 1 meaning the consumption spans over the whole month

            $reachedFinishMonth = ($currentMonth === $finishMonth) && ($currentYear === $finishYear);
            if ($reachedFinishMonth || $firstDayOfMonthConsumption !== 1) {
                $end = $reachedFinishMonth ? $finish : (clone $current)->modify('first day of next month');
                $ratio = $this->calculateMonthRatio($current, $end);
            }

            $hoursConsumedInCurrentMonth = ($ratio * self::HOURS_BY_MONTH[$currentMonth]);
            $totalHours += $hoursConsumedInCurrentMonth;
            
            if ($currentYear === $periodYear && $currentMonth >= $periodStartMonth && $currentMonth <= $periodEndMonth) {
                $isMonthOfPeriodStart = ($currentMonth === $periodStartMonth) && ($currentYear === $periodYear);
                $isMonthOfPeriodEnd = ($currentMonth === $periodEndMonth) && ($currentYear === $periodYear);
                $consumptionEndDay = intval($finish->format('j'));

                // recalculate hours consumed relative to the period if needed
                if ($isMonthOfPeriodStart && $periodStartDay > $firstDayOfMonthConsumption) {
                    $end = (clone $periodStart)->modify('first day of next month');
                    $ratio = $this->calculateMonthRatio($periodStart, $end);
                    $hoursConsumedInPeriod = $ratio * self::HOURS_BY_MONTH[$currentMonth];
                } else if ($isMonthOfPeriodEnd && $periodEndDay < $consumptionEndDay) {
                    $ratio = $this->calculateMonthRatio($current, $periodEnd);
                    $hoursConsumedInPeriod = $ratio * self::HOURS_BY_MONTH[$currentMonth];
                } else {
                    $hoursConsumedInPeriod = $hoursConsumedInCurrentMonth;
                }

                $hoursByMonth->setMonthValue($currentMonth, $hoursConsumedInPeriod);
            }

            $current->modify('first day of next month');
        }

        foreach ($hoursByMonth as $month => $hours) {
            if ($hours) {
                $consumptionByMonth->setMonthValue($month, intval(round(($hours / $totalHours) * $quantity)));
            }
        }

        return $consumptionByMonth;
    }

    public function getTotalConsumptionOfYears(Client $client, Context $context): array
    {
        $response = [];

        foreach ($context->getPeriods() as $period) {
            $year = intval($period->getStart()->format('Y'));
            $response[$year] = $this->getTotalConsumptionByPeriod($client, $period, $context->getDeliveryPoints())->getValues();
        }

        return $response;
    }

    public function getTotalConsumptionByPeriod(Client $client, Period $period, array $deliveryPoints = []): Year
    {
        // year is the same for the start and the end of the period
        $year = intval($period->getStart()->format('Y'));
        $consumptions = $this->invoiceConsumptionManager->getConsumptionsBetweenInterval($client, $period, $deliveryPoints);
        $consumptionsByMonth = $this->getTotalConsumptionQuantityByMonths($consumptions, $year, $period);

        return $consumptionsByMonth;
    }

    /**
     * This method intentionally uses plain arrays instead of doctrine objects because the dataset can be huge
     * and the performance would be abysmal if we were to use a naive implementation
     */
    public function getTotalAmountsBetweenInterval(Client $client, ?\DateTimeInterface $start = null, ?\DateTimeInterface $end = null): array
    {
        $result = [
            'consumption' => 0,
            'subscription' => 0,
            'taxes' => []
        ];

        $amountsInfo = $this->dpiManager->getAmountsBetweenInterval($client, $start, $end);
        if (empty($amountsInfo)) {
            return $result;
        }

        $ratiosByDpiIds = [];
        
        foreach ($amountsInfo as $amountInfo) {
            $ratio = $this->calculateDayRatio($amountInfo['consumptionStartedAt'], $amountInfo['consumptionFinishedAt'], $start, $end);
            $result['consumption'] += intval(round(($amountInfo['consumptionTotal'] ?? 0) * $ratio));
            $result['subscription'] += intval(round(($amountInfo['subscriptionTotal'] ?? 0) * $ratio));
            $ratiosByDpiIds[$amountInfo['dpiId']] = $ratio;
        }

        $taxesInfo = $this->invoiceTaxManager->getTaxesAmountsOfDeliveryPointInvoices(array_keys($ratiosByDpiIds));
        foreach($taxesInfo as $taxInfo) {
            $result['taxes'][$taxInfo['type']] ??= 0;
            $result['taxes'][$taxInfo['type']] += intval(round(($taxInfo['total'] ?? 0) * $ratiosByDpiIds[$taxInfo['dpiId']]));
        }

        return $result;
    }
}