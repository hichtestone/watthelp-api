<?php

declare(strict_types=1);

namespace App\OptionResolver\Invoice\Analysis;

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
            'invoices' => null,
            'status' => null,
        ]);

        $this->setAllowedTypes('ids', ['null', 'array']);
        $this->setAllowedTypes('invoices', ['null', 'array']);
        $this->setAllowedTypes('status', ['null', 'string']);
    }
}