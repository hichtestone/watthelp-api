<?php

declare(strict_types=1);

namespace App\OptionResolver\Budget;

use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteOptions extends OptionsResolver
{
    public function __construct()
    {
        $this->setDefaults([
            'ids' => null
        ]);

        $this->setAllowedTypes('ids', ['null', 'array', 'string']);
    }
}