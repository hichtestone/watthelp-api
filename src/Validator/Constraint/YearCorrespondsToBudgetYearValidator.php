<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Manager\BudgetManager;
use App\Query\Criteria;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class YearCorrespondsToBudgetYearValidator extends ConstraintValidator
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

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed                        $value      The value that should be validated
     * @param Constraint|SelectableGeneric $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value || !$constraint->budgetId) {
            return;
        }

        $value = \DateTime::createFromFormat('Y-m-d', $value);
        if (!$value) {
            return;
        }
        $year = intval($value->format('Y'));

        $authenticatedUser = $this->tokenStorage->getToken()->getUser();
        $budget = $this->budgetManager->getByCriteria(
            $authenticatedUser->getClient(),
            [new Criteria\Budget\Id($constraint->budgetId)]
        );
        if (!$budget) {
            return;
        }

        if ($year !== $budget->getYear()) {
            $this->context
                ->buildViolation('must_be_same_year_as_budget')
                ->addViolation();
        }
    }
}