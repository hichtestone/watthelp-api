<?php

declare(strict_types=1);

namespace App\OptionResolver\Notification;

use App\Entity\User;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteOptions extends OptionsResolver
{
    public function __construct()
    {
        $this->setDefaults([
            'ids' => null,
            'user' => null,
        ]);

        $this->setAllowedTypes('user', ['null', User::class]);
        $this->setAllowedTypes('ids', ['null', 'array', 'string']);
    }
}