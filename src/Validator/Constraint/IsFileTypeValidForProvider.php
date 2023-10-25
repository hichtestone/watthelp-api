<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class IsFileTypeValidForProvider extends Constraint
{
    public int $file;
    public string $provider;
}