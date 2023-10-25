<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice\Subscription;

use App\Analyzer\DeliveryPointInvoice\Subscription\TurpeAnalyzer;
use App\Entity\Contract;
use App\Entity\DeliveryPoint;
use App\Entity\Invoice;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceConsumption;
use App\Entity\Invoice\InvoiceSubscription;
use App\Entity\Pricing;
use App\Model\TranslationInfo;
use App\Model\Turpe\TurpeModel;
use App\Service\AmountConversionService;
use App\Service\TurpeService;
use App\Tests\WebTestCase;

/**
 * @group unit
 * @group analyzer
 * @group analyzer-turpe
 */
class TurpeAnalyzerTest extends WebTestCase
{
    public function testCanNotThrowIfDataMatch()
    {
        $contract = new Contract();
        $contract->setType(Pricing::TYPE_NEGOTIATED);

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setContract($contract);

        $consumption = new InvoiceConsumption();
        $consumption->setQuantity(3);

        $subscription = new InvoiceSubscription();
        $subscription->setTotal(40*10**5);
        $subscription->setStartedAt($startedAt = new \DateTime('2018-07-01'));
        $subscription->setFinishedAt($finishedAt = new \DateTime('2018-09-30'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setSubscription($subscription);
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setPowerSubscribed('4');

        $turpeMin = new TurpeModel(5*10**5, 10*10**5, 20*10**5, 1*10**5);
        $turpeMax= new TurpeModel(6*10**5, 12*10**5, 24*10**5, 1*10**5);

        $turpeServiceMock = $this->createMock(TurpeService::class);
        $turpeServiceMock->expects($this->once())->method('getTurpeInterval')
            ->with('4', $subscription->getStartedAt(), $subscription->getFinishedAt(), $consumption->getQuantity())
            ->willReturn([$turpeMin, $turpeMax]);

        $analyzer = $this->getAnalyzerMock(TurpeAnalyzer::class, [$turpeServiceMock, new AmountConversionService()]);
        $this->assertTrue($analyzer->supportsAnalysis($deliveryPointInvoice));
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanCatchAnomaly()
    {
        $contract = new Contract();
        $contract->setType(Pricing::TYPE_NEGOTIATED);

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->setContract($contract);

        $consumption = new InvoiceConsumption();
        $consumption->setQuantity(3);

        $subscription = new InvoiceSubscription();
        $subscription->setTotal(10*10**5);
        $subscription->setStartedAt($startedAt = new \DateTime('2018-07-01'));
        $subscription->setFinishedAt($finishedAt = new \DateTime('2018-09-30'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setSubscription($subscription);
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setPowerSubscribed('4');

        $turpeMin = new TurpeModel(5*10**5, 10*10**5, 20*10**5, 1*10**5);
        $turpeMax= new TurpeModel(6*10**5, 12*10**5, 24*10**5, 1*10**5);

        $turpeServiceMock = $this->createMock(TurpeService::class);
        $turpeServiceMock->expects($this->once())->method('getTurpeInterval')
            ->with('4', $subscription->getStartedAt(), $subscription->getFinishedAt(), $consumption->getQuantity())
            ->willReturn([$turpeMin, $turpeMax]);

        $turpeServiceMock->expects($this->once())->method('getTurpeDataInterval')
            ->with($subscription->getStartedAt(), $subscription->getFinishedAt())
            ->willReturn([
                'minAnnualCg' => 5*10**5, 'maxAnnualCg' => 6*10**5,
                'minAnnualCc' => 10*10**5, 'maxAnnualCc' => 12*10**5,
                'minCsCoeffPower' => 20*10**5, 'maxCsCoeffPower' => 24*10**5,
                'minCsCoeffEnergy' => 1*10**5, 'maxCsCoeffEnergy' => 1*10**5
            ]);

        $appliedRules = transInfo('turpe_applied_rules', [
            'power_subscribed' => 4.0,
            'consumption' => 3,
            'subscription_days' => 91,
            'from' => $startedAt,
            'to' => $finishedAt,
            'min_annual_cg' => 0.05,
            'max_annual_cg' => 0.06,
            'min_cg' => 0.05,
            'max_cg' => 0.06,
            'min_annual_cc' => 0.1,
            'max_annual_cc' => 0.12,
            'min_cc' => 0.1,
            'max_cc' => 0.12,
            'min_cs_coeff_power' => 0.2,
            'max_cs_coeff_power' => 0.24,
            'min_cs_coeff_energy' => 0.01,
            'max_cs_coeff_energy' => 0.01,
            'min_cs' => 0.21,
            'max_cs' => 0.25,
            'margin' => 0.01,
            'total_min' => 0.35,
            'total_max' => 0.44
        ]);

        $analyzer = $this->getAnalyzerMock(TurpeAnalyzer::class, [$turpeServiceMock, new AmountConversionService()]);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Invoice\Anomaly::TYPE_TURPE,
            transInfo('amount_incorrect', ['amount_type' => '', 'type' => 'TURPE']),
            $appliedRules, '0,10€', null,
            transInfo('expected_value_between_x_y', ['x' => '0,35€', 'y' => '0,44€']),
            'subscription.total'
        );
        $analyzer->analyze($deliveryPointInvoice);
    }
}