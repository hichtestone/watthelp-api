<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice\Consumption;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\Consumption\IndexDateAnalyzer;
use App\Entity\Invoice\Analysis\DeliveryPointInvoiceAnalysis;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceConsumption;
use App\Manager\Invoice\DeliveryPointInvoiceManager;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Service\LogService;
use App\Tests\WebTestCase;

/**
 * @group unit
 * @group analyzer
 * @group analyzer-consumption
 * @group analyzer-consumption-index-date
 */
class IndexDateAnalyzerTest extends WebTestCase
{
    public function testCanGetAnalyzerName()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = self::$container->get(LogService::class);
        $dpiManager = self::$container->get(DeliveryPointInvoiceManager::class);

        $analyzer = new IndexDateAnalyzer($translationManager, $logger, $dpiManager);

        $this->assertEquals('delivery_point_invoice.index_date', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = self::$container->get(LogService::class);
        $dpiManager = self::$container->get(DeliveryPointInvoiceManager::class);

        $analyzer = new IndexDateAnalyzer($translationManager, $logger, $dpiManager);

        $this->assertEquals(AnalyzerInterface::GROUP_CONSUMPTION, $analyzer->getGroup());
    }

    public function testCanThrowIgnoreEventIfWeDontHavePreviousInvoice()
    {
        $deliveryPointInvoice = new DeliveryPointInvoice();
        $dpia = new DeliveryPointInvoiceAnalysis();
        $deliveryPointInvoice->setDeliveryPointInvoiceAnalysis($dpia);

        $dpiManager = self::$container->get(DeliveryPointInvoiceManager::class);
        $analyzer = $this->getAnalyzerMock(IndexDateAnalyzer::class, [$dpiManager]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('no_previous_invoice_for_delivery_point'));
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeHaveNeitherIndexStartedAtNorIndexFinishedAt()
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

        $dpiManager = self::$container->get(DeliveryPointInvoiceManager::class);
        $analyzer = $this->getAnalyzerMock(IndexDateAnalyzer::class, [$dpiManager]);
        $analyzer->expects($this->exactly(2))->method('ignore')->withConsecutive(
            [transInfo('consumption_index_start_missing_in_current_invoice'), 'consumption.index_started_at'],
            [transInfo('consumption_index_finish_missing_in_previous_invoice')]
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeDontHaveInConsumptionDateIndex()
    {
        $consumption = new InvoiceConsumption();

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $previousConsumption = new InvoiceConsumption();
        $previousConsumption->setIndexFinishedAt(new \DateTime('2020-01-01'));
        $previous = new DeliveryPointInvoice();
        $previous->setConsumption($previousConsumption);

        $dpia = new DeliveryPointInvoiceAnalysis();
        $dpia->setPreviousDeliveryPointInvoice($previous);
        $deliveryPointInvoice->setDeliveryPointInvoiceAnalysis($dpia);

        $dpiManager = self::$container->get(DeliveryPointInvoiceManager::class);
        $analyzer = $this->getAnalyzerMock(IndexDateAnalyzer::class, [$dpiManager]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('consumption_index_start_missing_in_current_invoice'), 'consumption.index_started_at');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeDontHaveInPreviousConsumptionDateIndex()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setIndexStartedAt(new \DateTime('2018-01-01'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $previousConsumption = new InvoiceConsumption();

        $previous = new DeliveryPointInvoice();
        $previous->setConsumption($previousConsumption);

        $dpia = new DeliveryPointInvoiceAnalysis();
        $dpia->setPreviousDeliveryPointInvoice($previous);
        $deliveryPointInvoice->setDeliveryPointInvoiceAnalysis($dpia);

        $dpiManager = self::$container->get(DeliveryPointInvoiceManager::class);
        $analyzer = $this->getAnalyzerMock(IndexDateAnalyzer::class, [$dpiManager]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('consumption_index_finish_missing_in_previous_invoice'));
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowAnomalyEventIfDateIndexValueDoesNotMatch()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setIndexStartedAt($currentValue = new \DateTime('2018-01-01'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $previousConsumption = new InvoiceConsumption();
        $previousConsumption->setIndexFinishedAt($previousValue = new \DateTime('2018-01-04'));
        $previous = new DeliveryPointInvoice();
        $previous->setConsumption($previousConsumption);

        $dpia = new DeliveryPointInvoiceAnalysis();
        $dpia->setPreviousDeliveryPointInvoice($previous);
        $deliveryPointInvoice->setDeliveryPointInvoiceAnalysis($dpia);

        $anomalyDescription = transInfo('index_started_at_not_equal_to_previous_index_finished_at', [
            'index_started_at' => $currentValue,
            'previous_index_finished_at' => $previousValue
        ]);
        $dpiManager = self::$container->get(DeliveryPointInvoiceManager::class);
        $analyzer = $this->getAnalyzerMock(IndexDateAnalyzer::class, [$dpiManager]);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Anomaly::TYPE_DATE,
            $anomalyDescription, $anomalyDescription,
            $currentValue->format('d/m/Y'), $previousValue->format('d/m/Y'),
            transInfo('expected_value_give_or_take_two_days', ['expected_value' => $previousValue]),
            'consumption.index_started_at'
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanNotThrowIfDataMatch()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setIndexStartedAt($currentValue = new \DateTime('2018-01-01'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $previousConsumption = new InvoiceConsumption();
        $previousConsumption->setIndexFinishedAt($previousValue = new \DateTime('2018-01-01'));
        $previous = new DeliveryPointInvoice();
        $previous->setConsumption($previousConsumption);

        $dpia = new DeliveryPointInvoiceAnalysis();
        $dpia->setPreviousDeliveryPointInvoice($previous);
        $deliveryPointInvoice->setDeliveryPointInvoiceAnalysis($dpia);

        $dpiManager = self::$container->get(DeliveryPointInvoiceManager::class);
        $analyzer = $this->getAnalyzerMock(IndexDateAnalyzer::class, [$dpiManager]);
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->analyze($deliveryPointInvoice);
    }
}