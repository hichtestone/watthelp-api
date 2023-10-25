<?php

declare(strict_types=1);

namespace App\Exceptions;

use \Throwable;

class ImportException extends \Exception
{
    private array $errorMessages;

    public function __construct(array $errorMessages = [], int $code = 0, Throwable $previous = null)
    {
        parent::__construct(implode($errorMessages), $code, $previous);
        $this->errorMessages = $errorMessages;
    }

    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }
}