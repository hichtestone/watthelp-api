<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Entity\HasClientInterface;
use App\Query\Criteria\CriteriaInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsPricingTypeSameAsContractTypeValidator extends ConstraintValidator
{
    private EntityManagerInterface $entityManager;
    private TokenStorageInterface $tokenStorage;
    private RequestStack $requestStack;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed                        $value      The value that should be validated
     * @param Constraint|SelectableGeneric $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint): void
    {
        $pricing = $constraint->entity;
        if (!$pricing || empty($pricing->getContracts())) {
            return;
        }

        foreach ($pricing->getContracts() as $contract) {
            if ($contract->getType() !== $value) {
                $this->context
                    ->buildViolation('pricing_type_must_match_contract_type')
                    ->addViolation();
                return;
            }            
        }
    }
}