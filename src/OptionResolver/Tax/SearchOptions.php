<?php

declare(strict_types=1);

namespace App\OptionResolver\Tax;

use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchOptions extends OptionsResolver
{
    public function __construct()
    {
        $this->setDefaults([
            'id' => null,
            'exclude_ids' => null,
            'interval' => null
        ]);

        $this->setAllowedTypes('id', ['null', 'array']);
        $this->setAllowedTypes('exclude_ids', ['null', 'array']);
        $this->setAllowedTypes('interval', ['null', 'array']);
    }
}