<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConstraintValidatorException extends \Exception
{
    /**
     * @var ConstraintViolationListInterface
     */
    private ConstraintViolationListInterface $violations;

    public function __construct(ConstraintViolationListInterface $violations)
    {
        $this->violations = $violations;
        parent::__construct();
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}