<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Entity\HasBudgetInterface;
use App\Entity\HasUserInterface;
use App\Entity\User;
use App\Query\Criteria\CriteriaInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

class SelectableGenericValidator extends ConstraintValidator
{
    use ValidatorHelperTrait;

    private TranslatorInterface $translator;
    private EntityManagerInterface $entityManager;
    private TokenStorageInterface $tokenStorage;
    private RequestStack $requestStack;
    private ?TokenInterface $token = null;
    private ?User $authenticatedUser = null;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack
    )
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint|SelectableGeneric $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value) {
            return;
        }

        if (!$constraint->entity) {
            throw new \LogicException('The attribute "entity" must be implemented.');
        }

        if (!$constraint->criteria && !$constraint->criteriaCollection) {
            throw new \LogicException('The attribute "criteria" must be implemented.');
        }

        if ($constraint->criteria && $constraint->criteriaCollection) {
            throw new \LogicException('Use "criteria" or "criteriaCollection" parameters.');
        }

        $criteriaCollection = $this->getCriteriaCollection($constraint, $value);
        $repository = $this->entityManager->getRepository($constraint->entity);

        $criteriaParameters = $this->initGetByCriteriaParameters($this->getAuthenticatedUser()->getClient(), $constraint->entity, $repository);

        $criteriaParameters[] = $criteriaCollection;
        $entity = $repository->getByCriteria(...$criteriaParameters);
        if (!$entity) {
            $this->context
                ->buildViolation($this->translator->trans($constraint->notFoundMessage, [], $constraint->translatorDomain))
                ->addViolation();

            return;
        }

        if ($constraint->belongUser) {
            if (!$entity instanceof HasUserInterface) {
                $className = get_class($entity);
                throw new \LogicException(\sprintf('Class %s must implement %s to use belongUser option.', $className, HasUserInterface::class));
            }

            if ($this->getAuthenticatedUser()->getId() !== $entity->getUser()->getId()) {
                throw new \LogicException($this->translator->trans('entity_does_not_belong_to_user', [], 'validators'));
            }
        }

        if ($constraint->budgetId) {
            if (!$entity instanceof HasBudgetInterface) {
                $className = get_class($entity);
                throw new \LogicException(\sprintf('Class %s must implement %s to use the budgetId option.', $className, HasBudgetInterface::class));
            }
            if ($entity->getBudget()->getId() !== $constraint->budgetId) {
                throw new \LogicException($this->translator->trans('entity_does_not_belong_to_budget', ['entityId' => $entity->getId()], 'validators'));
            }
        }
    }

    /**
     * @param $value
     */
    public function getCriteriaCollection(SelectableGeneric $constraint, $value): array
    {
        $criteriaCollection = [];
        if ($constraint->criteria) {
            $class = $constraint->criteria;
            $criteriaCollection[] = new $class($value);
        }

        if ($constraint->criteriaCollection) {
            $request = $this->requestStack->getCurrentRequest();
            foreach ($constraint->criteriaCollection as $routeParam => $criteriaClass) {
                // routeParam is numeric if you don't pass the key on criteria collection.
                if (!is_numeric($routeParam) && null === $request->get((string)$routeParam)) {
                    throw new \LogicException(\sprintf('Route parameter %s doesn\'t not exist.', $routeParam));
                }

                $criteria = new $criteriaClass($request->get((string)$routeParam, $value));
                if (!$criteria instanceof CriteriaInterface) {
                    throw new \LogicException('Only class to implement CriteriaInterface are allowed.');
                }

                $criteriaCollection[] = $criteria;
            }
        }

        return $criteriaCollection;
    }

    private function getAuthenticatedUser(): User
    {
        $this->token ??= $this->tokenStorage->getToken();
        if (!$this->token || !($this->authenticatedUser ??= $this->token->getUser())) {
            throw new \LogicException('User must be connected');
        }
        return $this->authenticatedUser;
    }

}
