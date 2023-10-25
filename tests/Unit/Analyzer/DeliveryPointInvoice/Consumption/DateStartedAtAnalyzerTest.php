<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice\Consumption;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\Consumption\DateStartedAtAnalyzer;
use App\Entity\Invoice;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceConsumption;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Service\LogService;
use App\Tests\WebTestCase;

/**
 * @group unit
 * @group analyzer
 * @group analyzer-consumption
 * @group analyzer-consumption-date-started
 */
class DateStartedAtAnalyzerTest extends WebTestCase
{
    public function testCanGetAnalyzerName()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = self::$container->get(LogService::class);

        $analyzer = new DateStartedAtAnalyzer($translationManager, $logger);

        $this->assertEquals('delivery_point_invoice.consumption.date_started_at', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {        
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = self::$container->get(LogService::class);

        $analyzer = new DateStartedAtAnalyzer($translationManager, $logger);

        $this->assertEquals(AnalyzerInterface::GROUP_CONSUMPTION, $analyzer->getGroup());
    }

    public function testCanThrowIgnoreEventIfWeHaveNeitherIndexStartedDateNorStartedDateInConsumption()
    {
        $consumption = new InvoiceConsumption();

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $analyzer = $this->getAnalyzerMock(DateStartedAtAnalyzer::class);
        $analyzer->expects($this->exactly(2))->method('ignore')->withConsecutive(
            [transInfo('consumption_index_started_at_missing'), 'consumption.index_started_at'],
            [transInfo('started_at_missing', ['type' => 'consumption']), 'consumption.started_at']
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeDontHaveIndexStartedDateInConsumption()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setStartedAt(new \DateTime('2018-01-01'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $analyzer = $this->getAnalyzerMock(DateStartedAtAnalyzer::class);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('consumption_index_started_at_missing'), 'consumption.index_started_at');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeDontHaveStartedDateInConsumption()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setIndexStartedAt(new \DateTime('2018-01-01'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $analyzer = $this->getAnalyzerMock(DateStartedAtAnalyzer::class);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('started_at_missing', ['type' => 'consumption']), 'consumption.started_at');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowAnomalyEventIfWeStartedDateAndIndexStartedDateDoesNotMatch()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setIndexStartedAt($indexStartedAt = new \DateTime('2018-01-01'));
        $consumption->setStartedAt($startedAt = new \DateTime('2018-01-03'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $anomalyDescription = transInfo('index_started_at_not_equal_to_started_at', [
            'index_started_at' => $indexStartedAt,
            'started_at' => $startedAt
        ]);
        $analyzer = $this->getAnalyzerMock(DateStartedAtAnalyzer::class);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Invoice\Anomaly::TYPE_DATE,
            $anomalyDescription, $anomalyDescription,
            '03/01/2018', '01/01/2018', null, 'consumption.index_started_at'
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanNotThrowEventIfDataMatch()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setIndexStartedAt(new \DateTime('2018-01-01'));
        $consumption->setStartedAt(new \DateTime('2018-01-01'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $analyzer = $this->getAnalyzerMock(DateStartedAtAnalyzer::class);
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->analyze($deliveryPointInvoice);
    }
}