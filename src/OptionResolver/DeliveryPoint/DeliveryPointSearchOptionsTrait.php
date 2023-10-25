<?php

declare(strict_types=1);

namespace App\OptionResolver\DeliveryPoint;

use App\Entity\Contract;

trait DeliveryPointSearchOptionsTrait
{
    private function addSharedOptions(): void
    {
        $this->setDefaults([
            'reference' => null,
            'references' => null,
            'contract' => null,
            'code' => null,
            'no_invoice_for_months' => null,
            'is_in_scope' => null
        ]);

        $this->setAllowedTypes('reference', ['null', 'string']);
        $this->setAllowedTypes('references', ['null', 'array']);
        $this->setAllowedTypes('contract', ['null', Contract::class]);
        $this->setAllowedTypes('code', ['null', 'string']);
        $this->setAllowedTypes('no_invoice_for_months', ['null', 'string']);
        $this->setAllowedTypes('is_in_scope', ['null', 'boolean']);
    }
}