<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class XorPathValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof All) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\All');
        }

        if (null === $value) {
            return;
        }

        if (!is_array($value) && !$value instanceof \Traversable) {
            throw new UnexpectedTypeException($value, 'array or Traversable');
        }

        /** @var ExecutionContextInterface $context */
        $context = $this->context;
        /** @var RecursiveValidator $validator */
        $validator = $context->getValidator();

        foreach ($value as $key => $element) {
            $actionConstraint = null;

            /*
             * We will iterate all constraints to check all path
             * If one of the constraints is valid, we are able to validate it
             */
            foreach ($constraint->constraints as $constraints) {
                // first,we have to check which path is requested to know which constraint to apply
                $clone = $validator->startContext($context->getRoot());
                $clone->validate(
                    $element['path'],
                    $constraints->fields['path']->constraints
                );

                if (0 === \count($clone->getViolations())) {
                    $actionConstraint = $constraints;

                    break;
                }
            }

            if (null !== $actionConstraint) {
                $validator->inContext($context)
                    ->atPath('['.$key.']')
                    ->validate($element, $actionConstraint);
            } else {
                $context->buildViolation('Operation path is not valid.')->atPath('['.$key.'][path]')->addViolation();
            }
        }
    }
}
