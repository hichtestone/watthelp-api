<?php

declare(strict_types=1);

namespace App\OptionResolver\Invoice;

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
            'amount_ht' => null,
            'amount_tva' => null,
            'amount_ttc' => null,
            'emitted_at' => null,
            'has_analysis' => null
        ]);

        $this->setAllowedTypes('id', ['null', 'array']);
        $this->setAllowedTypes('exclude_ids', ['null', 'array']);
        $this->setAllowedTypes('reference', ['null', 'string']);
        $this->setAllowedTypes('references', ['null', 'array']);
        $this->setAllowedTypes('amount_ht', ['null', 'int']);
        $this->setAllowedTypes('amount_tva', ['null', 'int']);
        $this->setAllowedTypes('amount_ttc', ['null', 'int']);
        $this->setAllowedTypes('emitted_at', ['null', 'string']);
        $this->setAllowedTypes('has_analysis', ['null', 'boolean']);
    }
}