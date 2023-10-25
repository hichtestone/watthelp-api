<?php

declare(strict_types=1);

namespace App\OptionResolver\Role;

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
            'exclude_ids' => null,
            'name' => null,
            'description' => null,
            'users' => null,
            'permissions' => null
        ]);

        $this->setAllowedTypes('ids', ['null', 'array']);
        $this->setAllowedTypes('exclude_ids', ['null', 'array']);
        $this->setAllowedTypes('name', ['null', 'string']);
        $this->setAllowedTypes('description', ['null', 'string']);
        $this->setAllowedTypes('users', ['null', 'array']);
        $this->setAllowedTypes('permissions', ['null', 'array']);
    }
}