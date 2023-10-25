<?php

declare(strict_types=1);

namespace App\OptionResolver\ImportReport;

use App\Entity\User;
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
            'id' => null,
            'user' => null,
            'status' => null
        ]);

        $this->setAllowedTypes('id', ['null', 'array']);
        $this->setAllowedTypes('user', ['null', User::class]);
        $this->setAllowedTypes('status', ['null', 'string']);
    }
}