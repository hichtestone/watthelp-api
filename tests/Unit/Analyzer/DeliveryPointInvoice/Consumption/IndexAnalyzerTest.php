<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice\Consumption;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\Consumption\IndexAnalyzer;
use App\Entity\Invoice\Analysis\DeliveryPointInvoiceAnalysis;
use App\Entity\Invoice\Anomaly;
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
 * @group analyzer-consumption-index
 */
class IndexAnalyzerTest extends WebTestCase
{
    public function testCanGetAnalyzerName()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = self::$container->get(LogService::class);

        $analyzer = new IndexAnalyzer($translationManager, $logger);

        $this->assertEquals('delivery_point_invoice.index', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = self::$container->get(LogService::class);

        $analyzer = new IndexAnalyzer($translationManager, $logger);

        $this->assertEquals(AnalyzerInterface::GROUP_CONSUMPTION, $analyzer->getGroup());
    }

    public function testCanThrowIgnoreEventIfWeDontHavePreviousInvoice()
    {
        $deliveryPointInvoice = new DeliveryPointInvoice();
        $dpia = new DeliveryPointInvoiceAnalysis();
        $deliveryPointInvoice->setDeliveryPointInvoiceAnalysis($dpia);

        $analyzer = $this->getAnalyzerMock(IndexAnalyzer::class);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('no_previous_invoice_for_delivery_point'));
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeDontHaveInConsumptionIndex()
    {
        $consumption = new InvoiceConsumption();

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $previousConsumption = new InvoiceConsumption();
        $previousConsumption->setIndexFinish(12);
        $previous = new DeliveryPointInvoice();
        $previous->setConsumption($previousConsumption);

        $dpia = new DeliveryPointInvoiceAnalysis();
        $dpia->setPreviousDeliveryPointInvoice($previous);
        $deliveryPointInvoice->setDeliveryPointInvoiceAnalysis($dpia);

        $analyzer = $this->getAnalyzerMock(IndexAnalyzer::class);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('consumption_index_start_missing_in_current_invoice'), 'consumption.index_start');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeDontHaveInPreviousConsumptionIndex()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setIndexStart(1);

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $previousConsumption = new InvoiceConsumption();
        $previous = new DeliveryPointInvoice();
        $previous->setConsumption($previousConsumption);

        $dpia = new DeliveryPointInvoiceAnalysis();
        $dpia->setPreviousDeliveryPointInvoice($previous);
        $deliveryPointInvoice->setDeliveryPointInvoiceAnalysis($dpia);

        $analyzer = $this->getAnalyzerMock(IndexAnalyzer::class);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('consumption_index_finish_missing_in_previous_invoice'));
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeHaveNeitherIndexStartNorIndexFinish()
    {
        $consumption = new InvoiceConsumption();

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $previousConsumption = new InvoiceConsumption();
        $previous = new DeliveryPointInvoice();
        $previous->setConsumption($previousConsumption);

        $dpia = new DeliveryPointInvoiceAnalysis();
        $dpia->setPreviousDeliveryPointInvoice($previous);
        $deliveryPointInvoice->setDeliveryPointInvoiceAnalysis($dpia);

        $analyzer = $this->getAnalyzerMock(IndexAnalyzer::class);
        $analyzer->expects($this->exactly(2))->method('ignore')->withConsecutive(
            [transInfo('consumption_index_start_missing_in_current_invoice'), 'consumption.index_start'],
            [transInfo('consumption_index_finish_missing_in_previous_invoice')]
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowAnomalyEventIfIndexValueDoesNotMatch()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setIndexStart($currentValue = 2);

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $previousConsumption = new InvoiceConsumption();
        $previousConsumption->setIndexFinish($previousValue = 1);
        $previous = new DeliveryPointInvoice();
        $previous->setConsumption($previousConsumption);

        $dpia = new DeliveryPointInvoiceAnalysis();
        $dpia->setPreviousDeliveryPointInvoice($previous);
        $deliveryPointInvoice->setDeliveryPointInvoiceAnalysis($dpia);

        $analyzer = $this->getAnalyzerMock(IndexAnalyzer::class);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Anomaly::TYPE_INDEX,
            transInfo('index_start_not_equal_to_previous_index_finish'),
            '2 != 1', strval($currentValue), strval($previousValue),
            transInfo('expected_value', ['expected_value' => strval($previousValue)]),
            'consumption.index_start'
        );
        $analyzer->analyze($deliveryPointInvoice);
    }
   
    public function testCanNotThrowIfDataMatch()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setIndexStart($currentValue = 2);

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $previousConsumption = new InvoiceConsumption();
        $previousConsumption->setIndexFinish($previousValue = 2);
        $previous = new DeliveryPointInvoice();
        $previous->setConsumption($previousConsumption);

        $dpia = new DeliveryPointInvoiceAnalysis();
        $dpia->setPreviousDeliveryPointInvoice($previous);
        $deliveryPointInvoice->setDeliveryPointInvoiceAnalysis($dpia);

        $analyzer = $this->getAnalyzerMock(IndexAnalyzer::class);
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->analyze($deliveryPointInvoice);
    }
}