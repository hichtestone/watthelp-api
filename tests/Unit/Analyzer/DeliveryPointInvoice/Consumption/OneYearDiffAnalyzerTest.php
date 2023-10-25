<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice\Consumption;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\Consumption\OneYearDiffAnalyzer;
use App\Entity\DeliveryPoint;
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
 * @group analyzer-consumption-one-year-diff
 */
class OneYearDiffAnalyzerTest extends WebTestCase
{
    public function testCanGetAnalyzerName()
    {
        $translationManager = $this->createMock(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $manager = $this->createMock(DeliveryPointInvoiceManager::class);

        $analyzer = new OneYearDiffAnalyzer($translationManager, $logger, $manager);

        $this->assertEquals('delivery_point_invoice.consumption.one_year_diff', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {
        $translationManager = $this->createMock(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $manager = $this->createMock(DeliveryPointInvoiceManager::class);

        $analyzer = new OneYearDiffAnalyzer($translationManager, $logger, $manager);

        $this->assertEquals(AnalyzerInterface::GROUP_CONSUMPTION, $analyzer->getGroup());
    }

    public function testCanThrowIgnoreEventIfWeDontHaveConsumptionIndexFinishedAt()
    {
        $deliveryPoint = new DeliveryPoint();

        $consumption = new InvoiceConsumption();

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);

        $dpiManager = $this->createMock(DeliveryPointInvoiceManager::class);

        $analyzer = $this->getAnalyzerMock(OneYearDiffAnalyzer::class, [$dpiManager]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('consumption_index_finished_at_missing'), 'consumption.index_finished_at');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeDontHave2YearsOfData()
    {
        $deliveryPoint = new DeliveryPoint();

        $consumption = new InvoiceConsumption();
        $consumption->setIndexFinishedAt(new \DateTime('2018-01-01'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);

        $dpiManager = $this->createMock(DeliveryPointInvoiceManager::class);
        $dpiManager->expects($this->once())->method('hasBefore')
            ->with($deliveryPoint, new \DateTime('2016-01-01'))
            ->willReturn(false);

        $analyzer = $this->getAnalyzerMock(OneYearDiffAnalyzer::class, [$dpiManager]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('two_years_are_required'), 'consumption');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfWeDontHaveConsumptionForYearN1()
    {
        $deliveryPoint = new DeliveryPoint();

        $consumption = new InvoiceConsumption();
        $consumption->setIndexFinishedAt(new \DateTime('2018-01-01'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);

        $dpiManager = $this->createMock(DeliveryPointInvoiceManager::class);
        $dpiManager->expects($this->once())->method('hasBefore')
            ->with($deliveryPoint, new \DateTime('2016-01-01'))
            ->willReturn(true);
        $dpiManager->expects($this->once())->method('getSumConsumptionBetweenInterval')
            ->with($deliveryPoint, new \DateTime('2016-01-01'), new \DateTime('2017-01-01'))
            ->willReturn(0);

        $analyzer = $this->getAnalyzerMock(OneYearDiffAnalyzer::class, [$dpiManager]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('no_consumption_in_year', ['year' => 'previous']), 'consumption');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowAnomalyEventIfWeHaveDiffBetweenConsumptionBetween2YearsForPublicLight()
    {
        $deliveryPoint = new DeliveryPoint();

        $consumption = new InvoiceConsumption();
        $consumption->setIndexFinishedAt($indexFinishedAt = new \DateTime('2018-01-01'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);

        $dpiManager = $this->createMock(DeliveryPointInvoiceManager::class);
        $dpiManager->expects($this->once())->method('hasBefore')
            ->with($deliveryPoint, $twoYearsBefore = new \DateTime('2016-01-01'))
            ->willReturn(true);
        $dpiManager->expects($this->exactly(2))->method('getSumConsumptionBetweenInterval')
            ->withConsecutive(
                [$deliveryPoint, $twoYearsBefore, $oneYearBefore = new \DateTime('2017-01-01')],
                [$deliveryPoint, $oneYearBefore, $indexFinishedAt]
            )
            ->willReturnOnConsecutiveCalls(100, 106);

        $appliedRules = transInfo('one_year_diff_applied_rules', [
            'two_years_beforehand' => $twoYearsBefore,
            'one_year_beforehand' => $oneYearBefore,
            'consumption_finished_at' => $indexFinishedAt,
            'consumption_year_before_last' => 100,
            'percentage' => 6.0,
            'consumption_last_year' => 106
        ]);
        $analyzer = $this->getAnalyzerMock(OneYearDiffAnalyzer::class, [$dpiManager]);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Anomaly::TYPE_CONSUMPTION,
            transInfo('consumption_changed_more_than_x_percent', ['percentage' => 5]),
            $appliedRules, '106', '100', null,
            'consumption.quantity'
        );
        $analyzer->analyze($deliveryPointInvoice);
    }
}