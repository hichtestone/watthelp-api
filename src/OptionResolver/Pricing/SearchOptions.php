<?php

declare(strict_types=1);

namespace App\OptionResolver\Pricing;

use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchOptions extends OptionsResolver
{
    public function __construct()
    {
        $this->setDefaults([
            'id' => null,
            'exclude_ids' => null,
            'name' => null,
            'type' => null,
            'excluded_periods' => null,
            'started_at' => null,
            'finished_at' => null,
            'enabled' => null
        ]);

        $this->setAllowedTypes('id', ['null', 'array']);
        $this->setAllowedTypes('exclude_ids', ['null', 'array']);
        $this->setAllowedTypes('name', ['null', 'string']);
        $this->setAllowedTypes('type', ['null', 'string']);
        $this->setAllowedTypes('excluded_periods', ['null', 'array']);
        $this->setAllowedTypes('started_at', ['null', 'string']);
        $this->setAllowedTypes('finished_at', ['null', 'string']);
        $this->setAllowedTypes('enabled', ['null', 'string']);
    }
}