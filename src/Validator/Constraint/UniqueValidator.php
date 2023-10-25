<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Query\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\RuntimeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UniqueValidator extends ConstraintValidator
{
    use ValidatorHelperTrait;

    protected EntityManagerInterface $entityManager;
    protected TokenStorageInterface $tokenStorage;
    private RequestStack $requestStack;
    private ContainerInterface $container;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        ContainerInterface $container
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->container = $container;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed             $value      The value that should be validated
     * @param Constraint|Unique $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token || !($authenticatedUser = $token->getUser())) {
            throw new RuntimeException('User must be connected');
        }

        if (!$constraint->class) {
            throw new ConstraintDefinitionException('Validator class parameter must be defined.');
        }

        if (!$constraint->criteria) {
            throw new ConstraintDefinitionException('Validator criteria parameter must be defined.');
        }

        $criteriaClass = $constraint->criteria;
        $criteria = new $criteriaClass($value);
        if (!$criteria instanceof Criteria\CriteriaInterface) {
            throw new ConstraintDefinitionException('Validator parameter criteria class must implements CriteriaInterface.');
        }

        $repository = $this->entityManager->getRepository($constraint->class);
        $criteriaParameters = $this->initGetByCriteriaParameters($authenticatedUser->getClient(), $constraint->class, $repository);
        
        $criteriaParameters[] = [$criteria];
        $exist = $repository->getByCriteria(...$criteriaParameters);

        // Build violation if necessary
        if ($exist && $exist->getId() !== $constraint->existingId) {
            $this->context->buildViolation($constraint->errorMessage)->addViolation();
            return;
        }
    }
}