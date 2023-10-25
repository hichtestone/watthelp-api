<?php

declare(strict_types=1);

namespace App\OptionResolver\Permission;

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
            'codes' => null,
            'group' => null
        ]);

        $this->setAllowedTypes('ids', ['null', 'array']);
        $this->setAllowedTypes('codes', ['null', 'array']);
        $this->setAllowedTypes('group', ['null', 'string']);
    }
}