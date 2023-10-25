<?php

declare(strict_types=1);

namespace App\Model;

class TranslationInfo
{
    private string $key;
    private array $params;

    public function __construct(string $key, array $params = [])
    {
        $this->key = $key;
        $this->params = $params;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function __toString(): string
    {
        $params = json_encode($this->params);
        return "Key: {$this->key} - Params: $params";
    }
}