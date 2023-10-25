<?php

namespace App\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class Unique extends Constraint
{
    public string $errorMessage = 'This value is already used.';

    public ?int $existingId = null;
    public string $class = '';
    public string $criteria = '';
}