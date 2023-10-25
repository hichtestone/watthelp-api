<?php

declare(strict_types=1);

namespace App\OptionResolver\Contract;

use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchOptions extends OptionsResolver
{
    public function __construct()
    {
        $this->setDefaults([
            'id' => null,
            'exclude_ids' => null,
            'reference' => null,
            'references' => null,
            'provider' => null,
            'type' => null,
            'invoice_period' => null,
            'started_at' => null,
            'finished_at' => null
        ]);

        $this->setAllowedTypes('id', ['null', 'array']);
        $this->setAllowedTypes('exclude_ids', ['null', 'array']);
        $this->setAllowedTypes('reference', ['null', 'string']);
        $this->setAllowedTypes('references', ['null', 'array']);
        $this->setAllowedTypes('provider', ['null', 'string']);
        $this->setAllowedTypes('type', ['null', 'string']);
        $this->setAllowedTypes('invoice_period', ['null', 'string']);
        $this->setAllowedTypes('started_at', ['null', 'string']);
        $this->setAllowedTypes('finished_at', ['null', 'string']);
    }
}
