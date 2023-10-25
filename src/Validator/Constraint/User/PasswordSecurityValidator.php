<?php

declare(strict_types=1);

namespace App\Validator\Constraint\User;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordSecurityValidator extends ConstraintValidator
{
    private int $passwordLength;
    private bool $letterRequired;
    private bool $caseDiffRequired;
    private bool $numberRequired;
    private bool $specialCharRequired;

    /**
     * @param int  $passwordLength
     * @param bool $letterRequired
     * @param bool $caseDiffRequired
     * @param bool $numberRequired
     * @param bool $specialCharRequired
     */
    public function __construct(
        int $passwordLength,
        bool $letterRequired,
        bool $caseDiffRequired,
        bool $numberRequired,
        bool $specialCharRequired
    ) {
        $this->passwordLength = $passwordLength;
        $this->letterRequired = $letterRequired;
        $this->caseDiffRequired = $caseDiffRequired;
        $this->numberRequired = $numberRequired;
        $this->specialCharRequired = $specialCharRequired;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed                       $value      The value that should be validated
     * @param Constraint|PasswordSecurity $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        $errors = [];
        if ($this->passwordLength > 0 && (mb_strlen($value) < $this->passwordLength)) {
            $errors[] = $constraint->tooShort;
        }
        if ($this->letterRequired && !preg_match('/\pL/u', $value)) {
            $errors[] = $constraint->missingLetters;
        }
        if ($this->caseDiffRequired && !preg_match('/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u', $value)) {
            $errors[] = $constraint->missingCaseDiff;
        }
        if ($this->numberRequired && !preg_match('/\pN/u', $value)) {
            $errors[] = $constraint->missingNumbers;
        }
        if ($this->specialCharRequired && !preg_match('/[^p{Ll}\p{Lu}\pL\pN]/u', $value)) {
            $errors[] = $constraint->missingSpecialCharacter;
        }

        if (!empty($errors)) {
            $this->context->buildViolation(sprintf('Votre mot de passe doit contenir au moins %s.', implode(', ', $errors)))
                ->setParameters(['{{length}}' => $this->passwordLength])
                ->setInvalidValue($value)
                ->addViolation();
        }
    }
}
