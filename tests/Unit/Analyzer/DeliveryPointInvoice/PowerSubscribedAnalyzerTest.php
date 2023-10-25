<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\PowerSubscribedAnalyzer;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Service\LogService;
use App\Tests\WebTestCase;

/**
 * @group unit
 * @group analyzer
 * @group analyzer-power-subscribed
 */
class PowerSubscribedAnalyzerTest extends WebTestCase
{
    public function testCanGetAnalyzerName()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);

        $analyzer = new PowerSubscribedAnalyzer($translationManager, $logger);

        $this->assertEquals('delivery_point_invoice.power_subscribed', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);

        $analyzer = new PowerSubscribedAnalyzer($translationManager, $logger);

        $this->assertEquals(AnalyzerInterface::GROUP_DEFAULT, $analyzer->getGroup());
    }

    public function testCanThrowIgnoreEventIfWeDontHavePowerSubscribed()
    {
        $deliveryPointInvoice = new DeliveryPointInvoice();

        $analyzer = $this->getAnalyzerMock(PowerSubscribedAnalyzer::class);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('power_subscribed_missing'), 'power_subscribed');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowAnomalyEventIfPowerSubscribedIsAboveMax()
    {
        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setPowerSubscribed('41.7');

        $anomalyDescription = transInfo('power_subscribed_incorrect', ['min' => 0.1, 'max' => 36.0]);
        $analyzer = $this->getAnalyzerMock(PowerSubscribedAnalyzer::class);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Anomaly::TYPE_CONSUMPTION,
            $anomalyDescription, $anomalyDescription, '41,7 kWh', null, 
            transInfo('expected_value_between_x_y', ['x' => '0,1 kWh', 'y' => '36,0 kWh']),
            'power_subscribed'
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowAnomalyEventIfPowerSubscribedIsBelowMin()
    {
        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setPowerSubscribed('0');

        $anomalyDescription = transInfo('power_subscribed_incorrect', ['min' => 0.1, 'max' => 36.0]);
        $analyzer = $this->getAnalyzerMock(PowerSubscribedAnalyzer::class);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Anomaly::TYPE_CONSUMPTION,
            $anomalyDescription, $anomalyDescription, '0,0 kWh', null, 
            transInfo('expected_value_between_x_y', ['x' => '0,1 kWh', 'y' => '36,0 kWh']),
            'power_subscribed'
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanNotThrowIfDataMatch()
    {
        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setPowerSubscribed('12.2');

        $analyzer = $this->getAnalyzerMock(PowerSubscribedAnalyzer::class);
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->analyze($deliveryPointInvoice);
    }
}