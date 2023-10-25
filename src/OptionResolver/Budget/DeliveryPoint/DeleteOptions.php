<?php

declare(strict_types=1);

namespace App\OptionResolver\Budget\DeliveryPoint;

use App\Entity\Budget;
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
            'budget' => null,
            'ids' => null,
        ]);

        $this->setAllowedTypes('budget', ['null', Budget::class]);
        $this->setAllowedTypes('ids', ['null', 'array', 'string']);
    }
}