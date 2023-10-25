<?php

declare(strict_types=1);

namespace App\OptionResolver\Invoice\DeliveryPointInvoice;

use App\Entity\DeliveryPoint;
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
            'delivery_point' => null,
            'emitted_at' => null,
            'is_credit_note' => null,
            'delivery_point_reference' => null,
            'delivery_point_name' => null,
            'invoice_reference' => null
        ]);

        $this->setAllowedTypes('ids', ['null', 'array']);
        $this->setAllowedTypes('delivery_point', ['null', DeliveryPoint::class]);
        $this->setAllowedTypes('emitted_at', ['null', 'datetime', 'array']);
        $this->setAllowedTypes('is_credit_note', ['null', 'boolean']);
        $this->setAllowedTypes('delivery_point_reference', ['null', 'string']);
        $this->setAllowedTypes('delivery_point_name', ['null', 'string']);
        $this->setAllowedTypes('invoice_reference', ['null', 'string']);
    }
}