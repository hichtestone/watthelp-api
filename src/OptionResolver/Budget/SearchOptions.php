<?php

declare(strict_types=1);

namespace App\OptionResolver\Budget;

use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchOptions extends OptionsResolver
{
    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function __construct()
    {
        $this->setDefaults([
            'ids' => null,
            'year' => null,
            'years' => null,
            'max_year' => null,
        ]);
        $this->setAllowedTypes('ids', ['null', 'array']);
        $this->setAllowedTypes('year', ['null', 'integer']);
        $this->setAllowedTypes('years', ['null', 'array']);
        $this->setAllowedTypes('max_year', ['null', 'integer']);
    }
}
