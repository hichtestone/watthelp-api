<?php

declare(strict_types=1);

namespace App\Request\Validator\Stats\Consumption\BudgetComparison;

use App\Query\Criteria;
use App\Manager\BudgetManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\CollectionValidator;

class StatsConstraintListValidator extends CollectionValidator
{
    private BudgetManager $budgetManager;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        BudgetManager $budgetManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->budgetManager = $budgetManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function validate($data, Constraint $constraint)
    {
        if (isset($data['period']) && isset($data['period']['start']) && isset($data['period']['end'])) {
            $start = \DateTime::createFromFormat('Y-m-d', $data['period']['start']);
            $end   = \DateTime::createFromFormat('Y-m-d', $data['period']['end']);
            if ($start && $end) {
                if ($end <= $start) {
                    $this->context->buildViolation('period_start_must_be_before_period_end')
                        ->atPath('period')
                        ->addViolation();
                }
                if ($start->format('Y') !== $end->format('Y')) {
                    $this->context->buildViolation('period_must_belong_to_same_year')
                        ->atPath('period')
                        ->addViolation();
                } else if ($start->format('n') === $end->format('n')) {
                    $this->context->buildViolation('period_cant_belong_to_same_month')
                        ->atPath('period')
                        ->addViolation();                    
                } else {
                    // check a budget exists in the year of the period provided
                    $authenticatedUser = $this->tokenStorage->getToken()->getUser();
                    $budget = $this->budgetManager->getByCriteria(
                        $authenticatedUser->getClient(),
                        [new Criteria\Budget\Year(intval($start->format('Y')))]
                    );

                    if (!$budget) {
                        $this->context->buildViolation('budget_of_year_does_not_exist')
                            ->atPath('period')
                            ->addViolation();
                    }
                }
            }
        }

        parent::validate($data, $constraint);
    }
}
