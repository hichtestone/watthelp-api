<?php

declare(strict_types=1);

namespace App\Query\Criteria;

abstract class AbstractCriteria implements CriteriaInterface
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getCriteria()
    {
        return $this->value;
    }
}