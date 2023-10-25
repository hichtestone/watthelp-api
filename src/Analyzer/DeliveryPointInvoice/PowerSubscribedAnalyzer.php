<?php

declare(strict_types=1);

namespace App\Analyzer\DeliveryPointInvoice;

use App\Analyzer\AbstractAnalyzer;
use App\Analyzer\AnalyzerInterface;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Model\TranslationInfo;

class PowerSubscribedAnalyzer extends AbstractAnalyzer implements AnalyzerInterface
{
    public function analyze(DeliveryPointInvoice $dpi): void
    {
        if (is_null($dpi->getPowerSubscribed())) {
            $this->ignore(transInfo('power_subscribed_missing'), 'power_subscribed');
            return;
        }

        $powerSubscribed = floatval($dpi->getPowerSubscribed());
        $min = 0.1;
        $max = 36;

        if ($powerSubscribed < $min || $powerSubscribed > $max) {
            $powerSubscribed = number_format($powerSubscribed, 1, ',', ' ') . ' kWh';
            $minFormatted = number_format($min, 1, ',', ' ') . ' kWh';
            $maxFormatted = number_format($max, 1, ',', ' ') . ' kWh';
            $anomalyDescription = transInfo('power_subscribed_incorrect', ['min' => $min, 'max' => $max]);
            $this->anomaly(
                Anomaly::TYPE_CONSUMPTION,
                $anomalyDescription,
                $anomalyDescription,
                $powerSubscribed,
                null,
                transInfo('expected_value_between_x_y', ['x' => $minFormatted, 'y' => $maxFormatted]),
                'power_subscribed'
            );
        }
    }

    public function getName(): string
    {
        return 'delivery_point_invoice.power_subscribed';
    }

    public function getGroup(): string
    {
        return AnalyzerInterface::GROUP_DEFAULT;
    }

    public function getPriority(): int
    {
        return 1;
    }
}