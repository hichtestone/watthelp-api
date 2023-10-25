<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class GreaterThanOrEqualValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     * @param $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        $minValue = $this->findPropertyPathValue($this->context->getRoot(), $constraint->propertyPath);

        if (empty($value) || empty($minValue)) {
            return;
        }

        if ($value < $minValue) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ compared_value }}', $minValue)
                ->addViolation();
        }
    }

    /**
     * @return void|mixed
     */
    private function findPropertyPathValue(array $data, string $needle)
    {
        foreach ($data as $key => $element) {
            if ($key === $needle) {
                return $element;
            }

            if (is_array($element)) {
                return $this->findPropertyPathValue($element, $needle);
            }
        }

        return;
    }
}