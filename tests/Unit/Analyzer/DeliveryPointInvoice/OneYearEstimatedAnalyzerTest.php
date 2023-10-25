<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\OneYearEstimatedAnalyzer;
use App\Entity\DeliveryPoint;
use App\Entity\Invoice;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Manager\Invoice\DeliveryPointInvoiceManager;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Service\LogService;
use App\Tests\WebTestCase;

/**
 * @group unit
 * @group analyzer
 * @group analyzer-one-year-estimated
 */
class OneYearEstimatedAnalyzerTest extends WebTestCase
{

    public function testCanGetAnalyzerName()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $manager = $this->createMock(DeliveryPointInvoiceManager::class);

        $analyzer = new OneYearEstimatedAnalyzer($translationManager, $logger, $manager);

        $this->assertEquals('delivery_point_invoice.one_year_estimated', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $manager = $this->createMock(DeliveryPointInvoiceManager::class);

        $analyzer = new OneYearEstimatedAnalyzer($translationManager, $logger, $manager);

        $this->assertEquals(AnalyzerInterface::GROUP_DEFAULT, $analyzer->getGroup());
    }

    public function testAnalyzerCanIgnoreIfWeDontHaveOneIndexFinishedAtInConsumption()
    {
        $consumption = new Invoice\InvoiceConsumption();

        $deliveryPoint = new DeliveryPoint();

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);

        $manager = $this->createMock(DeliveryPointInvoiceManager::class);

        $analyzer = $this->getAnalyzerMock(OneYearEstimatedAnalyzer::class, [$manager]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('consumption_index_finished_at_missing'), 'consumption.index_finished_at');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testAnalyzerCanIgnoreIfWeDontHaveOneYearOfDataForADeliveryPoint()
    {
        $date = new \DateTime('2015-01-01');
        $oneYear = new \DateTime('2014-01-01');

        $consumption = new Invoice\InvoiceConsumption();
        $consumption->setIndexFinishedAt($date);

        $deliveryPoint = new DeliveryPoint();

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);

        $manager = $this->createMock(DeliveryPointInvoiceManager::class);
        $manager->expects($this->once())
            ->method('hasBefore')
            ->with($deliveryPoint, $oneYear)
            ->willReturn(false);

        $analyzer = $this->getAnalyzerMock(OneYearEstimatedAnalyzer::class, [$manager]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('no_delivery_point_invoice_at_least_one_year_old'));
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testAnalyzerCanThrowAnomalyIfAllInvoiceDuringOneYearAreEstimated()
    {
        $date = new \DateTime('2015-01-01');
        $oneYear = new \DateTime('2014-01-01');

        $consumption = new Invoice\InvoiceConsumption();
        $consumption->setIndexFinishedAt($date);

        $deliveryPoint = new DeliveryPoint();

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);

        $manager = $this->createMock(DeliveryPointInvoiceManager::class);
        $manager->expects($this->once())
            ->method('hasBefore')
            ->with($deliveryPoint, $oneYear)
            ->willReturn(true);

        $manager->expects($this->once())
            ->method('hasRealInvoiceBetweenInterval')
            ->with($deliveryPoint, $oneYear, $date)
            ->willReturn(false);

        $analyzer = $this->getAnalyzerMock(OneYearEstimatedAnalyzer::class, [$manager]);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Invoice\Anomaly::TYPE_DATE, transInfo('no_real_invoice_for_more_than_a_year')
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testAnalyzerCannotThrowIfEverythingOk()
    {
        $date = new \DateTime('2015-01-01');
        $oneYear = new \DateTime('2014-01-01');

        $consumption = new Invoice\InvoiceConsumption();
        $consumption->setIndexFinishedAt($date);

        $deliveryPoint = new DeliveryPoint();

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);

        $manager = $this->createMock(DeliveryPointInvoiceManager::class);
        $manager->expects($this->once())
            ->method('hasBefore')
            ->with($deliveryPoint, $oneYear)
            ->willReturn(true);

        $manager->expects($this->once())
            ->method('hasRealInvoiceBetweenInterval')
            ->with($deliveryPoint, $oneYear, $date)
            ->willReturn(true);

        $analyzer = $this->getAnalyzerMock(OneYearEstimatedAnalyzer::class, [$manager]);
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->analyze($deliveryPointInvoice);
    }
}