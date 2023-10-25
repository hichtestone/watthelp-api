<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Model\TranslationInfo;
use \Throwable;

class IgnoreException extends \Exception
{
    private ?string $field;
    private TranslationInfo $transMessage;

    public function __construct(TranslationInfo $message, ?string $field = null, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(strval($message), $code, $previous);
        $this->transMessage = $message;
        $this->field = $field;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function getTransMessage(): TranslationInfo
    {
        return $this->transMessage;
    }
}