<?php

declare(strict_types=1);

namespace App\OptionResolver\Invoice;

use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteOptions extends OptionsResolver
{
    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function __construct()
    {
        $this->setDefaults([
            'ids' => null,
            'references' => null
        ]);

        $this->setAllowedTypes('ids', ['null', 'array']);
        $this->setAllowedTypes('references', ['null', 'array']);
    }
}