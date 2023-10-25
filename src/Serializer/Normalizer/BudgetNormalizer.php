<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Budget;
use App\Manager\BudgetManager;
use App\Manager\Invoice\InvoiceConsumptionManager;
use App\Service\ConsumptionService;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class BudgetNormalizer implements ContextAwareNormalizerInterface
{
    private BudgetManager $budgetManager;
    private ObjectNormalizer $normalizer;
    private InvoiceConsumptionManager $invoiceConsumptionManager;
    private ConsumptionService $consumptionService;

    public function __construct(
        BudgetManager $budgetManager,
        ObjectNormalizer $normalizer,
        InvoiceConsumptionManager $invoiceConsumptionManager,
        ConsumptionService $consumptionService
    ) {
        $this->budgetManager = $budgetManager;
        $this->normalizer = $normalizer;
        $this->invoiceConsumptionManager = $invoiceConsumptionManager;
        $this->consumptionService = $consumptionService;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \Symfony\Component\Serializer\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Serializer\Exception\LogicException
     */
    public function normalize($budget, string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($budget, $format, $context);

        $expands = $context['groups'] ?? [];
        if (in_array(Budget::EXPAND_DATA_CALCULATED_INFO, $expands)) {
            $consumptions = $this->invoiceConsumptionManager->getConsumptionsOfYear($budget->getClient(), $budget->getYear());
            $calculated = $this->consumptionService->getTotalCalculatedConsumptionsByYear($consumptions, $budget->getYear());
            $data = array_merge($data, $calculated);
        }

        if (in_array(Budget::EXPAND_DATA_PREVIOUS_BUDGET, $expands)) {
            $previousBudget = $this->budgetManager->getPrevious($budget);
            // remove previous budget expand to make sure we aren't including the previous year + the previous of the previous etc
            $context['groups'] = array_diff($expands, [Budget::EXPAND_DATA_PREVIOUS_BUDGET]);
            $data['previous'] = $previousBudget ? $this->normalize($previousBudget, $format, $context) : null;
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Budget;
    }
}