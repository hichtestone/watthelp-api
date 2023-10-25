<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice\Consumption;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\Consumption\QuantityAnalyzer;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceConsumption;
use App\Exceptions\IgnoreException;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Service\ConsumptionService;
use App\Service\LogService;
use App\Tests\WebTestCase;

/**
 * @group unit
 * @group analyzer
 * @group analyzer-consumption
 * @group analyzer-consumption-quantity
 */
class QuantityAnalyzerTest extends WebTestCase
{
    public function testCanGetAnalyzerName()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $service = $this->createMock(ConsumptionService::class);

        $analyzer = new QuantityAnalyzer($translationManager, $logger, $service);

        $this->assertEquals('delivery_point_invoice.consumption.quantity', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $service = $this->createMock(ConsumptionService::class);

        $analyzer = new QuantityAnalyzer($translationManager, $logger, $service);

        $this->assertEquals(AnalyzerInterface::GROUP_CONSUMPTION, $analyzer->getGroup());
    }

    public function testCanThrowIgnoreEventIfWeDontHaveInConsumptionQuantity()
    {
        $consumption = new InvoiceConsumption();

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $service = $this->createMock(ConsumptionService::class);
        $analyzer = $this->getAnalyzerMock(QuantityAnalyzer::class, [$service]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('consumption_quantity_missing'), 'consumption.quantity');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeDontHaveInConsumptionIndexFinish()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setQuantity(999);

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $service = $this->createMock(ConsumptionService::class);
        $service->expects($this->once())->method('getConsumptionQuantity')
            ->with($consumption)
            ->willThrowException(new IgnoreException(transInfo('index_finish_missing'), 'consumption.index_finish'));

        $analyzer = $this->getAnalyzerMock(QuantityAnalyzer::class, [$service]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('index_finish_missing'));
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowAnomalyEventIfWeQuantityDoesNotMatch()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setIndexFinish(1001);
        $consumption->setIndexStart(1);
        $consumption->setQuantity(999);

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $service = $this->createMock(ConsumptionService::class);
        $service->expects($this->once())->method('getConsumptionQuantity')
            ->with($consumption)
            ->willReturn(1000);

        $anomalyDescription = transInfo('consumption_not_equal_to_indexes_difference', [
            'consumption' => 999,
            'finish_index' => 1001,
            'start_index' => 1
        ]);
        $analyzer = $this->getAnalyzerMock(QuantityAnalyzer::class, [$service]);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Anomaly::TYPE_CONSUMPTION,
            $anomalyDescription, $anomalyDescription, '999', null,
            transInfo('expected_value', ['expected_value' => '1000']),
            'consumption.quantity'
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanNotThrowIfDataMatch()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setIndexFinish(1001);
        $consumption->setIndexStart(1);
        $consumption->setQuantity(1000);

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $service = $this->createMock(ConsumptionService::class);
        $service->expects($this->once())->method('getConsumptionQuantity')
            ->with($consumption)
            ->willReturn(1000);

        $analyzer = $this->getAnalyzerMock(QuantityAnalyzer::class, [$service]);
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->analyze($deliveryPointInvoice);
    }
}