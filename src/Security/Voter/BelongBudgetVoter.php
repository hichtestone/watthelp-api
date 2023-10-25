<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Budget;
use App\Entity\HasBudgetInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Contracts\Translation\TranslatorInterface;

class BelongBudgetVoter extends Voter
{
    private TranslatorInterface $translator;

    public const BELONG_BUDGET = 'BELONG_BUDGET';

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, $subject): bool
    {
        return self::BELONG_BUDGET === $attribute && is_array($subject);
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $budget = array_filter($subject, fn($item) => $item instanceof Budget);
        $budget = array_pop($budget);
        $entity = array_filter($subject, fn($item) => $item instanceof HasBudgetInterface);
        $entity = array_pop($entity);

        if (!$budget || !$entity) {
            throw new \LogicException('If you want to use the BELONG_BUDGET restriction, you should specify the budget and an entity implementing HasBudgetInterface in the subject of the annotation.');
        }

        // can't return directly as of sept 2020 because SensioFrameworkExtraBundle doesn't handle
        // correctly the error when subject is an array
        // it should be fine to return directly when/if https://github.com/sensiolabs/SensioFrameworkExtraBundle/pull/630 is merged
        if ($budget->getId() !== $entity->getBudget()->getId()) {
            throw new AccessDeniedHttpException($this->translator->trans('entity_does_not_belong_to_budget', ['entityId' => $entity->getId()], 'validators'));
        }

        return true;
    }
}