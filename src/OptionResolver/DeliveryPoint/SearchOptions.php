<?php

declare(strict_types=1);

namespace App\OptionResolver\DeliveryPoint;

use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchOptions extends OptionsResolver
{
    use DeliveryPointSearchOptionsTrait;

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function __construct()
    {
        $this->setDefaults([
            'ids' => null,
            'exclude_ids' => null
        ]);

        $this->setAllowedTypes('ids', ['null', 'array']);
        $this->setAllowedTypes('exclude_ids', ['null', 'array']);

        $this->addSharedOptions();
    }
}