<?php

declare(strict_types=1);

namespace App\OptionResolver\Budget\DeliveryPoint;

use App\Entity\Budget;
use App\Entity\Contract;
use App\Entity\DeliveryPoint;
use App\OptionResolver\DeliveryPoint\DeliveryPointSearchOptionsTrait;
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
            'budget' => null,
            'delivery_point' => null,
            'delivery_points' => null,
            'year' => null,
        ]);
        $this->setAllowedTypes('delivery_point', ['null', DeliveryPoint::class]);
        $this->setAllowedTypes('delivery_points', ['null', 'array']);
        $this->setAllowedTypes('budget', ['null', Budget::class]);
        $this->setAllowedTypes('year', ['null', 'integer']);

        $this->addSharedOptions();
    }
}