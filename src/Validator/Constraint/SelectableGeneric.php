<?php

declare(strict_types=1);

namespace App\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class SelectableGeneric extends Constraint
{
    public string $notFoundMessage = 'Selected value does not exist.';
    public string $translatorDomain = 'constraints';
    public string $criteria = '';
    public array $criteriaCollection = [];
    public string $entity = '';
    public bool $belongUser = false;
    public int $budgetId = 0;
}
