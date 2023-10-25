<?php

declare(strict_types=1);

namespace App\Request\Validator\Pricing;

use App\Entity\Pricing;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\CollectionValidator;

class PricingConstraintListValidator extends CollectionValidator
{
    /**
     * Checks if the passed value is valid.
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     * @throws \Exception
     */
    public function validate($value, Constraint $constraint)
    {
        if (array_key_exists('type', $value) && $value['type'] === Pricing::TYPE_REGULATED && !isset($value['subscription_price'])) {
            $this->context->buildViolation('subscription_price_must_be_provided_if_regulated')
                ->atPath('[subscription_price]')
                ->addViolation();
        }
        if (array_key_exists('type', $value) && $value['type'] === Pricing::TYPE_NEGOTIATED && isset($value['subscription_price'])) {
            $this->context->buildViolation('subscription_price_cant_be_provided_if_negotiated')
                ->atPath('[subscription_price]')
                ->addViolation();            
        }

        parent::validate($value, $constraint);
    }
}