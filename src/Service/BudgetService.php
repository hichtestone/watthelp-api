<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Budget;
use App\Entity\Budget\DeliveryPointBudget;
use App\Entity\Client;
use App\Manager\BudgetManager;
use App\Manager\Budget\DeliveryPointBudgetManager;
use App\Manager\DeliveryPointManager;
use App\Manager\Invoice\InvoiceConsumptionManager;
use App\Model\ConsumedBudget;
use App\Model\Period;
use App\Model\Year;
use App\Query\Criteria;
use Symfony\Contracts\Translation\TranslatorInterface;

class BudgetService
{
    use ConsumptionCalculationTrait;

    private BudgetManager $budgetManager;
    private DeliveryPointBudgetManager $dpbManager;
    private DeliveryPointManager $deliveryPointManager;
    private InvoiceConsumptionManager $invoiceConsumptionManager;
    private ConsumptionService $consumptionService;
    private TranslatorInterface $translator;

    public function __construct(
        BudgetManager $budgetManager,
        DeliveryPointBudgetManager $dpbManager,
        DeliveryPointManager $deliveryPointManager,
        InvoiceConsumptionManager $invoiceConsumptionManager,
        ConsumptionService $consumptionService,
        TranslatorInterface $translator
    ) {
        $this->budgetManager = $budgetManager;
        $this->dpbManager = $dpbManager;
        $this->deliveryPointManager = $deliveryPointManager;
        $this->invoiceConsumptionManager = $invoiceConsumptionManager;
        $this->consumptionService = $consumptionService;
        $this->translator = $translator;
    }

    public function getForecastBudgetByMonth(ConsumedBudget $consumedBudget, int $initialBudget): Year
    {
        $budgetByMonth = new Year(null);
        $consumedBudgetByMonth = $consumedBudget->getBudgetByMonth();

        foreach ($consumedBudgetByMonth as $currentMonth => $currentConsumedMonthBudget) {
            if (is_null($currentConsumedMonthBudget)) {
                $budgetByMonth->setCurrentMonth($currentMonth);
                // if this is the beginning of the forecast, initialize the previous month value of the forecast
                // with the previous value of the consumed budget to prevent gaps in the graph
                if ($currentMonth >= Year::FEBRUARY && is_null($budgetByMonth->getPreviousMonthValue()) && !is_null($consumedBudgetByMonth->getPreviousMonthValue())) {
                    $budgetByMonth->setPreviousMonthValue($consumedBudgetByMonth->getPreviousMonthValue());
                }
                $previousMonthBudget = $currentMonth === Year::JANUARY ? $initialBudget : $budgetByMonth->getPreviousMonthValue();
                $monthBudget = intval(round((ConsumptionService::HOURS_BY_MONTH[$currentMonth] / $consumedBudget->getTotalHoursConsumed()) * $consumedBudget->getTotalBudgetConsumed()));
                $budgetByMonth->setCurrentMonthValue($previousMonthBudget - $monthBudget);
            }
        }

        return $budgetByMonth;
    }

    public function getConsumedBudgetByMonth(int $budgetAmount, int $averagePrice, int $year, Client $client): ConsumedBudget
    {
        $consumptions = $this->invoiceConsumptionManager->getConsumptionsOfYear($client, $year);
        $consumptionsByMonth = $this->consumptionService->getTotalConsumptionQuantityByMonths($consumptions, $year);

        $consumedBudget = new ConsumedBudget();
        $budgetByMonth = new Year(null);
        $lastMonthBudget = $budgetAmount;

        foreach ($budgetByMonth as $month => $budget) {
            $currentMonthlyConsumption = $consumptionsByMonth->getMonthValue($month);
            if ($currentMonthlyConsumption) {
                $budgetAmount -= ($currentMonthlyConsumption * $averagePrice);
                $budgetByMonth->setCurrentMonthValue($budgetAmount);
                $consumedBudget->addTotalBudgetConsumed($lastMonthBudget - $budgetAmount);
                $consumedBudget->addTotalHoursConsumed(ConsumptionService::HOURS_BY_MONTH[$month]);
                $lastMonthBudget = $budgetAmount;
            } else {
                // check if there is no consumption for the rest of the year
                $remainingConsumptions = array_filter(array_slice($consumptionsByMonth->getMonths(), $month-1));
                if (empty($remainingConsumptions)) {
                    break;
                }
                $budgetByMonth->setCurrentMonthValue($budgetAmount);
            }
        }

        $consumedBudget->setBudgetByMonth($budgetByMonth);

        return $consumedBudget;
    }

    /**
     * Returns the expected budget amount for each month -> €
     */
    public function getExpectedBudgetByMonth(int $consumption, int $budgetAmount, int $averagePrice): Year
    {
        $yearlyBudget = new Year();
        $totalHoursInYear = array_sum(ConsumptionService::HOURS_BY_MONTH);

        foreach ($yearlyBudget as $month => $budget) {
            $consumptionOfMonth = intval(round($consumption/100 * (ConsumptionService::HOURS_BY_MONTH[$month]/$totalHoursInYear)));
            $budgetAmountOfMonth = $consumptionOfMonth * $averagePrice;
            $budgetAmount -= $budgetAmountOfMonth;
            $yearlyBudget->setCurrentMonthValue($budgetAmount);
        }

        return $yearlyBudget;
    }

    /**
     * Returns the expected budget consumption for each month in the period -> kWh
     */
    public function getExpectedBudgetConsumptionByMonth(Client $client, Period $period, array $deliveryPoints = []): Year
    {
        $yearlyBudget = new Year(null);
        $totalHoursInYear = array_sum(ConsumptionService::HOURS_BY_MONTH);

        $budgetConsumption = 0;
        $budget = $this->budgetManager->getByCriteria($client, [new Criteria\Budget\Year($period->getYear())]);
        if (!$budget) {
            throw new \LogicException($this->translator->trans('budget_of_year_does_not_exist', [], 'validators'));
        }
        if ($deliveryPoints) {
            $dpbs = iterator_to_array($this->dpbManager->findByFilters([
                'delivery_points' => $deliveryPoints,
                'budget' => $budget
            ]));
            foreach ($dpbs as $dpb) {
                $budgetConsumption += $dpb->getTotalConsumption();
            }
        } else {
            $budgetConsumption = $budget->getTotalConsumption();
        }

        $start = $period->getStart();
        $startMonth = intval($start->format('n'));
        $end = $period->getEnd();
        $endMonth = intval($end->format('n'));
        foreach ($yearlyBudget as $month => $monthConsumption) {

            $periodRatio = 1; // between 0 and 1, 1 meaning the whole month is in the period
            if ($month < $startMonth || $month > $endMonth) {
                continue; // month out of the period, ignore it
            } else if ($month === $startMonth) {
                $nextMonth = (clone $start)->modify('first day of next month');
                $periodRatio = $this->calculateMonthRatio($start, $nextMonth);
            } else if ($month === $endMonth) {
                $beginningOfMonth = (clone $end)->modify('first day of this month');
                $periodRatio = $this->calculateMonthRatio($beginningOfMonth, $end);
            }

            $consumptionOfMonth = $budgetConsumption * $periodRatio * (ConsumptionService::HOURS_BY_MONTH[$month]/$totalHoursInYear);
            $consumptionOfMonth = intval(round($consumptionOfMonth));
            $yearlyBudget->setCurrentMonthValue($consumptionOfMonth);
        }

        return $yearlyBudget;
    }

    /**
     * Creates and initializes the DeliveryPointBudgets of the Budget provided
     * Fills the DeliveryPointBudgets with the previous year budget if it exists
     * Otherwise, calculates an estimation of each DeliveryPointBudget from the InvoiceConsumption of the previous year
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function createDeliveryPointBudgets(Budget $budget, ?Budget $previousBudget): Budget
    {
        $previousDeliveryPointBudgets = [];
        if ($previousBudget) {
            foreach ($previousBudget->getDeliveryPointBudgets() as $prevDpBudget) {
                $previousDeliveryPointBudgets[$prevDpBudget->getDeliveryPoint()->getId()] = $prevDpBudget;
            }
        }

        $deliveryPoints = $this->deliveryPointManager->findByFilters($budget->getClient(), []);
        $totalConsumption = 0;

        foreach ($deliveryPoints as $deliveryPoint) {
            $deliveryPointBudget = new DeliveryPointBudget();
            $deliveryPointBudget->setBudget($budget);
            $deliveryPointBudget->setDeliveryPoint($deliveryPoint);
            $deliveryPointBudget->setRenovation(false);

            // try to fill values with previous year
            $previousDeliveryPointBudget = $previousDeliveryPointBudgets[$deliveryPoint->getId()] ?? null;
            if ($previousDeliveryPointBudget) {
                $deliveryPointBudget = $this->fillByPrevious($deliveryPointBudget, $previousDeliveryPointBudgets[$deliveryPoint->getId()]);   
            }

            // calculate consumption estimation with last year invoices
            // if it was not set by previous year AND the DeliveryPointBudget wasn't renovated last year
            if (is_null($deliveryPointBudget->getTotalConsumption()) &&
                (!$previousDeliveryPointBudget || !$previousDeliveryPointBudget->isRenovation())) {
                $dpTotalConsumptionQuantity = $this->calculateConsumptionQuantity($deliveryPointBudget);
                $deliveryPointBudget->setTotalConsumption($dpTotalConsumptionQuantity);
            }

            $dpTotalConsumptionQuantity = intval(round(($deliveryPointBudget->getTotalConsumption() ?? 0) / 100));
            $total = $dpTotalConsumptionQuantity * ($budget->getAveragePrice() ?? 0);
            $deliveryPointBudget->setTotal($total);

            $budget->addDeliveryPointBudget($deliveryPointBudget);
            $totalConsumption += $deliveryPointBudget->getTotalConsumption() ?? 0;
        }

        if (is_null($budget->getTotalConsumption())) {
            $budget->setTotalConsumption($totalConsumption);
        } else {
            $totalConsumption = $budget->getTotalConsumption();
        }

        if (is_null($budget->getTotalAmount()) && !is_null($budget->getAveragePrice())) {
            $totalAmount = intval(round(($totalConsumption/100) * $budget->getAveragePrice(), 2));
            $budget->setTotalAmount($totalAmount);
        }

        return $budget;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function calculateConsumptionQuantity(DeliveryPointBudget $deliveryPointBudget): int
    {
        $previousYear = $deliveryPointBudget->getBudget()->getYear()-1;

        $lastYearConsumptions = $this->invoiceConsumptionManager->getConsumptionsOfYear($deliveryPointBudget->getBudget()->getClient(), $previousYear, [$deliveryPointBudget->getDeliveryPoint()]);

        $previousYearConsumptionQuantity = $this->consumptionService->getTotalConsumptionQuantityByMonths($lastYearConsumptions, $previousYear);
        $previousYearConsumptionQuantity = array_sum(array_values($previousYearConsumptionQuantity->getMonths()));

        return $previousYearConsumptionQuantity * 100;
    }

    private function fillByPrevious(DeliveryPointBudget $deliveryPointBudget, DeliveryPointBudget $previousDeliveryPointBudget)
    {
        if ($previousDeliveryPointBudget->isRenovation()) {
            $deliveryPointBudget->setInstalledPower($previousDeliveryPointBudget->getNewInstalledPower());
            $deliveryPointBudget->setEquipmentPowerPercentage($previousDeliveryPointBudget->getNewEquipmentPowerPercentage());
            $deliveryPointBudget->setGradation($previousDeliveryPointBudget->getNewGradation());
            $deliveryPointBudget->setGradationHours($previousDeliveryPointBudget->getNewGradationHours());
            $deliveryPointBudget->setSubTotalConsumption($previousDeliveryPointBudget->getNewSubTotalConsumption());
            $deliveryPointBudget->setTotalConsumption($previousDeliveryPointBudget->getNewSubTotalConsumption());
        } else {
            $deliveryPointBudget->setInstalledPower($previousDeliveryPointBudget->getInstalledPower());
            $deliveryPointBudget->setEquipmentPowerPercentage($previousDeliveryPointBudget->getEquipmentPowerPercentage());
            $deliveryPointBudget->setGradation($previousDeliveryPointBudget->getGradation());
            $deliveryPointBudget->setGradationHours($previousDeliveryPointBudget->getGradationHours());
            $deliveryPointBudget->setSubTotalConsumption($previousDeliveryPointBudget->getSubTotalConsumption());
            $deliveryPointBudget->setTotalConsumption($previousDeliveryPointBudget->getSubTotalConsumption());
        }

        return $deliveryPointBudget;
    }
}