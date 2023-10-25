<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class FileType extends Constraint
{
    public string $errorMessage = 'mime-type-is-not-allowed';

    public array $allowedMimeTypes = [];
}
