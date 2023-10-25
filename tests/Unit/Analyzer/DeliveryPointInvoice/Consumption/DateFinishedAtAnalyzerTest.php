<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice\Consumption;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\Consumption\DateFinishedAtAnalyzer;
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
 * @group analyzer-consumption-date-finished
 */
class DateFinishedAtAnalyzerTest extends WebTestCase
{
    public function testCanGetAnalyzerName()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logService = self::$container->get(LogService::class);

        $analyzer = new DateFinishedAtAnalyzer($translationManager, $logService);

        $this->assertEquals('delivery_point_invoice.consumption.date_finished_at', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logService = self::$container->get(LogService::class);

        $analyzer = new DateFinishedAtAnalyzer($translationManager, $logService);

        $this->assertEquals(AnalyzerInterface::GROUP_CONSUMPTION, $analyzer->getGroup());
    }

    public function testCanThrowIgnoreEventIfWeHaveNeitherIndexFinishedDateNorFinishedDateInConsumption()
    {
        $consumption = new InvoiceConsumption();

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $analyzer = $this->getAnalyzerMock(DateFinishedAtAnalyzer::class);
        $analyzer->expects($this->exactly(2))->method('ignore')->withConsecutive(
            [transInfo('consumption_index_finished_at_missing'), 'consumption.index_finished_at'],
            [transInfo('finished_at_missing', ['type' => 'consumption']), 'consumption.finished_at']
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeDontHaveIndexFinishedDateInConsumption()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setFinishedAt(new \DateTime('2018-01-01'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $analyzer = $this->getAnalyzerMock(DateFinishedAtAnalyzer::class);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('consumption_index_finished_at_missing'), 'consumption.index_finished_at');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeDontHaveFinishedDateInConsumption()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setIndexFinishedAt(new \DateTime('2018-01-01'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $analyzer = $this->getAnalyzerMock(DateFinishedAtAnalyzer::class);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('finished_at_missing', ['type' => 'consumption']), 'consumption.finished_at');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowAnomalyEventIfWeFinishedDateAndIndexFinishedDateDoesNotMatch()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setIndexFinishedAt($indexFinishedAt = new \DateTime('2018-01-01'));
        $consumption->setFinishedAt($finishedAt = new \DateTime('2018-01-03'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $anomalyDescription = transInfo('index_finished_at_not_equal_to_finished_at', [
            'index_finished_at' => $indexFinishedAt,
            'finished_at' => $finishedAt
        ]);
        $analyzer = $this->getAnalyzerMock(DateFinishedAtAnalyzer::class);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Invoice\Anomaly::TYPE_DATE,
            $anomalyDescription, $anomalyDescription,
            '03/01/2018', '01/01/2018', null, 'consumption.index_finished_at'
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanNotThrowEventIfDataMatch()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setIndexFinishedAt(new \DateTime('2018-01-01'));
        $consumption->setFinishedAt(new \DateTime('2018-01-01'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $analyzer = $this->getAnalyzerMock(DateFinishedAtAnalyzer::class);
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->analyze($deliveryPointInvoice);
    }
}