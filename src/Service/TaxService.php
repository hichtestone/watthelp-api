<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\City;
use App\Entity\Client;
use App\Entity\Invoice\InvoiceConsumption;
use App\Exceptions\IgnoreException;
use App\Manager\TaxManager;
use App\Model\TranslationInfo;

class TaxService
{
    private TaxManager $taxManager;

    public function __construct(TaxManager $taxManager)
    {
        $this->taxManager = $taxManager;
    }

    /**
     * @throws IgnoreException
     */
    public function getTaxAmounts(InvoiceConsumption $consumption, Client $client): array
    {
        if (!$startedAt = $consumption->getIndexStartedAt()) {
            throw new IgnoreException(
                transInfo('consumption_index_started_at_missing'),
                'consumption.index_started_at'
            );
        }

        if (!$finishedAt = $consumption->getIndexFinishedAt()) {
            throw new IgnoreException(
                transInfo('consumption_index_started_at_missing'),
                'consumption.index_finished_at'
            );
        }

        $taxes = $this->taxManager->findByFilters($client, [
            'interval' => [
                'from' => $startedAt,
                'to' => $finishedAt
            ]
        ]);

        if (empty($taxes)) {
            throw new IgnoreException(transInfo('no_pricing_found_in_period', 
                [
                    'from' => $startedAt->format(DateFormatService::ANALYZER),
                    'to'   => $finishedAt->format(DateFormatService::ANALYZER)
                ]
            ));
        }

        $minCspe  = $maxCspe  = null;
        $minTdcfe = $maxTdcfe = null;
        $minTccfe = $maxTccfe = null;
        $minCta   = $maxCta   = null;

        $updateMinMax = function (?int &$min, ?int &$max, int $amount) {
            if (!$min || $min > $amount) {
                $min = $amount;
            }
            if (!$max || $max < $amount) {
                $max = $amount;
            }
        };

        foreach($taxes as $tax) {
            $updateMinMax($minCspe, $maxCspe, $tax->getCspe());
            $updateMinMax($minTdcfe, $maxTdcfe, $tax->getTdcfe());
            $updateMinMax($minTccfe, $maxTccfe, $tax->getTccfe());
            $updateMinMax($minCta, $maxCta, $tax->getCta());
        }

        return [
            'cspe'  => [$minCspe, $maxCspe],
            'tdcfe' => [$minTdcfe, $maxTdcfe],
            'tccfe' => [$minTccfe, $maxTccfe],
            'cta'   => [$minCta, $maxCta]
        ];
    }
}