<?php

declare(strict_types=1);

namespace App\Request\Validator\Stats\Budget;

use App\Manager\BudgetManager;
use App\Query\Criteria;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\CollectionValidator;

class StatsConstraintListValidator extends CollectionValidator
{
    private TokenStorageInterface $tokenStorage;
    private BudgetManager $budgetManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        BudgetManager $budgetManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->budgetManager = $budgetManager;
    }

    /**
     * Checks if the passed value is valid.
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     * @throws \Exception
     */
    public function validate($value, Constraint $constraint)
    {
        parent::validate($value, $constraint);

        if (array_key_exists('year', $value)) {
            $year = $value['year'];
            $authenticatedUser = $this->tokenStorage->getToken()->getUser();
            $budget = $this->budgetManager->getByCriteria(
                $authenticatedUser->getClient(),
                [new Criteria\Budget\Year($year)]
            );
            if ($budget) {
                if (is_null($budget->getAveragePrice()) || is_null($budget->getTotalConsumption()) || is_null($budget->getTotalAmount())) {
                    $this->context->buildViolation('budget_of_year_is_incomplete', ['year' => $year])
                        ->addViolation();
                }
            }
        }
    }
}