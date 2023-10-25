<?php

declare(strict_types=1);

namespace App\Service;

use App\Exceptions\IgnoreException;
use App\Model\TranslationInfo;
use App\Model\Turpe\TurpeData;
use App\Model\Turpe\TurpeModel;
use App\Service\DateFormatService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class TurpeService
{
    private Collection $data;

    public function __construct()
    {
        $this->data = new ArrayCollection();
        $this->data->add(new TurpeData(696, 1980, 5856, 1.38, new \DateTime('2017-08-01'), new \DateTime('2018-07-31')));
        $this->data->add(new TurpeData(1188, 2028, 5856, 1.38, new \DateTime('2018-08-01'), new \DateTime('2019-07-31')));
        $this->data->add(new TurpeData(1272, 2040, 6060, 1.43, new \DateTime('2019-08-01'), new \DateTime('2020-07-31')));
        $this->data->add(new TurpeData(1344, 2088, 6252, 1.47, new \DateTime('2020-08-01')));
    }

    /**
     * @throws IgnoreException
     */
    public function getTurpeInterval(string $powerSubscribed, \DateTimeInterface $from, \DateTimeInterface $to, int $consumptionQty, int $invoicePeriod = 0): array
    {
        [
            'minAnnualCg' => $minAnnualCg, 'maxAnnualCg' => $maxAnnualCg,
            'minAnnualCc' => $minAnnualCc, 'maxAnnualCc' => $maxAnnualCc,
            'minCsCoeffPower' => $minCsCoeffPower, 'maxCsCoeffPower' => $maxCsCoeffPower,
            'minCsCoeffEnergy' => $minCsCoeffEnergy, 'maxCsCoeffEnergy' => $maxCsCoeffEnergy
        ] = $this->getTurpeDataInterval($from, $to);

        $minAnnualCsFixed = $minCsCoeffPower * floatval($powerSubscribed);
        $maxAnnualCsFixed = $maxCsCoeffPower * floatval($powerSubscribed);
        $minCsVariable = $minCsCoeffEnergy ? $minCsCoeffEnergy * $consumptionQty : 0;
        $maxCsVariable = $maxCsCoeffEnergy ? $maxCsCoeffEnergy * $consumptionQty : 0;

        $days = $from->diff($to)->days;

        if ($invoicePeriod) {
            $minCG = (int) (round($minAnnualCg / 12) * $invoicePeriod);
            $maxCG = (int) (round($maxAnnualCg / 12) * $invoicePeriod);
            $minCC = (int) (round($minAnnualCc / 12) * $invoicePeriod);
            $maxCC = (int) (round($maxAnnualCc / 12) * $invoicePeriod);
            $minCSFixed = (int) (round($minAnnualCsFixed / 12) * $invoicePeriod);
            $maxCSFixed = (int) (round($maxAnnualCsFixed / 12) * $invoicePeriod);
        } else {
            $minCG = (int) (round($minAnnualCg / 365) * $days);
            $maxCG = (int) (round($maxAnnualCg / 365) * $days);
            $minCC = (int) (round($minAnnualCc / 365) * $days);
            $maxCC = (int) (round($maxAnnualCc / 365) * $days);
            $minCSFixed = (int) (round($minAnnualCsFixed / 365) * $days);
            $maxCSFixed = (int) (round($maxAnnualCsFixed / 365) * $days);
        }

        $turpeMin = new TurpeModel($minCG, $minCC, $minCSFixed, $minCsVariable);
        $turpeMax = new TurpeModel($maxCG, $maxCC, $maxCSFixed, $maxCsVariable);

        return [$turpeMin, $turpeMax];
    }

    /**
     * @throws IgnoreException
     */
    public function getTurpeDataInterval(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        $turpeData = $this->data->filter(fn (TurpeData $t) => 
            $t->getStartedAt() <= $to
            && (is_null($t->getFinishedAt()) || $t->getFinishedAt() >= $from)
        );

        if (count($turpeData) === 0) {
            throw new IgnoreException(
                transInfo('turpe_missing_in_period', [
                    'from' => $from->format(DateFormatService::ANALYZER),
                    'to'   => $to->format(DateFormatService::ANALYZER)
                ])
            );
        }

        $minAnnualCg = $maxAnnualCg = null;
        $minAnnualCc = $maxAnnualCc = null;
        $minCsCoeffPower = $maxCsCoeffPower = null;
        $minCsCoeffEnergy = $maxCsCoeffEnergy = null;

        $updateMinMax = function (?int &$min, ?int &$max, int $amount) {
            if (!$min || $min > $amount) {
                $min = $amount;
            }
            if (!$max || $max < $amount) {
                $max = $amount;
            }
        };

        foreach($turpeData as $turpe) {
            $updateMinMax($minAnnualCg, $maxAnnualCg, $turpe->getCg());
            $updateMinMax($minAnnualCc, $maxAnnualCc, $turpe->getCc());
            $updateMinMax($minCsCoeffPower, $maxCsCoeffPower, $turpe->getCsCoeffPower());
            $updateMinMax($minCsCoeffEnergy, $maxCsCoeffEnergy, $turpe->getCsCoeffEnergy());
        }

        return [
            'minAnnualCg' => $minAnnualCg, 'maxAnnualCg' => $maxAnnualCg,
            'minAnnualCc' => $minAnnualCc, 'maxAnnualCc' => $maxAnnualCc,
            'minCsCoeffPower' => $minCsCoeffPower, 'maxCsCoeffPower' => $maxCsCoeffPower,
            'minCsCoeffEnergy' => $minCsCoeffEnergy, 'maxCsCoeffEnergy' => $maxCsCoeffEnergy
        ];
    }
}