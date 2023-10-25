<?php

declare(strict_types=1);

namespace App\OptionResolver\Notification;

use App\Entity\User;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchOptions extends OptionsResolver
{
    public function __construct()
    {
        $this->setDefaults([
            'user' => null,
            'id' => null,
            'message' => null,
            'is_read' => null,
        ]);

        $this->setAllowedTypes('user', ['null', User::class]);
        $this->setAllowedTypes('id', ['null', 'string', 'array']);
        $this->setAllowedTypes('message', ['null', 'string']);
        $this->setAllowedTypes('is_read', ['null', 'boolean']);
    }
}
