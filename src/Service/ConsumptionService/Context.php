<?php

declare(strict_types=1);

namespace App\Service\ConsumptionService;

use App\Entity\DeliveryPoint;
use App\Factory\PeriodFactory;
use App\Model\Period;

class Context
{
    /** @var Period[] */
    private array $periods = [];

    /** @var DeliveryPoint[] */
    private array $deliveryPoints = [];
 
    public function __construct(array $params)
    {
        $period = array_map('intval', $params['period'] ?? []);
        $periodStartDay = $period['start_day'] ?? 1;
        $periodStartMonth = $period['start_month'] ?? 1;
        $periodEndDay = $period['end_day'] ?? 31;
        $periodEndMonth = $period['end_month'] ?? 12;

        foreach ($params['years'] as $year) {
            $this->periods[] = PeriodFactory::createFromSplitUpDates(intval($year), $periodStartDay, $periodStartMonth, $periodEndDay, $periodEndMonth);
        }
    }

    /**
     * @return Period[]
     */
    public function getPeriods(): array
    {
        return $this->periods;
    }

    public function setDeliveryPoints(array $deliveryPoints): void
    {
        $this->deliveryPoints = $deliveryPoints;
    }

    /**
     * @return DeliveryPoint[]
     */
    public function getDeliveryPoints(): array
    {
        return $this->deliveryPoints;
    }
}