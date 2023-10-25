<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class GreaterThanOrEqual extends Constraint
{
    public string $propertyPath = '';
    public string $message = 'This value should be greater than or equal to {{ compared_value }}.';

    public function __construct($options = null)
    {
        parent::__construct($options);
    }
}