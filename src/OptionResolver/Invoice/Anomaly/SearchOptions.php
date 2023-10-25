<?php

declare(strict_types=1);

namespace App\OptionResolver\Invoice\Anomaly;

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
            'invoices' => null,
            'invoice_reference' => null,
            'status'=> null,
            'created' => null,
            'total' => null,
            'total_percentage' => null,
            'profit' => null
        ]);

        $this->setAllowedTypes('id', ['null', 'string', 'array']);
        $this->setAllowedTypes('invoices', ['null', 'array']);
        $this->setAllowedTypes('invoice_reference', ['null', 'string']);
        $this->setAllowedTypes('status', ['null', 'string']);
        $this->setAllowedTypes('created', ['null', 'array']);
        $this->setAllowedTypes('total', ['null', 'int']);
        $this->setAllowedTypes('total_percentage', ['null', 'float']);
        $this->setAllowedTypes('profit', ['null', 'string']);
    }
}

