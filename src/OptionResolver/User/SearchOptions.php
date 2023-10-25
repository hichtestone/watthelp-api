<?php

declare(strict_types=1);

namespace App\OptionResolver\User;

use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchOptions extends OptionsResolver
{
    public function __construct()
    {
        $this->setDefaults([
            'id' => null,
            'first_name' => null,
            'last_name' => null,
            'email' => null,
            'phone' => null,
            'mobile' => null,
            'exclude_ids' => null,
        ]);

        $this->setAllowedTypes('id', ['null', 'array']);
        $this->setAllowedTypes('first_name', ['null', 'string']);
        $this->setAllowedTypes('last_name', ['null', 'string']);
        $this->setAllowedTypes('email', ['null', 'string']);
        $this->setAllowedTypes('phone', ['null', 'string']);
        $this->setAllowedTypes('mobile', ['null', 'string']);
        $this->setAllowedTypes('exclude_ids', ['null', 'array']);
    }
}
