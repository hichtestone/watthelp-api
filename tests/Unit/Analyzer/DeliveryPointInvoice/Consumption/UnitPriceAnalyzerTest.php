<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice\Consumption;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\Consumption\UnitPriceAnalyzer;
use App\Entity\DeliveryPoint;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceConsumption;
use App\Entity\Pricing;
use App\Manager\PricingManager;
use App\Manager\TranslationManager;
use App\Model\TranslationInfo;
use App\Service\AmountConversionService;
use App\Service\DateFormatService;
use App\Service\LogService;
use App\Tests\WebTestCase;

/**
 * @group unit
 * @group analyzer
 * @group analyzer-consumption
 * @group analyzer-consumption-unit-price
 */
class UnitPriceAnalyzerTest extends WebTestCase
{
    public function testCanGetAnalyzerName()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $service = $this->createMock(PricingManager::class);

        $analyzer = new UnitPriceAnalyzer($translationManager, $logger, $service, new AmountConversionService());

        $this->assertEquals('delivery_point_invoice.consumption.unit_price', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {
        $translationManager = self::$container->get(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $service = $this->createMock(PricingManager::class);

        $analyzer = new UnitPriceAnalyzer($translationManager, $logger, $service, new AmountConversionService());

        $this->assertEquals(AnalyzerInterface::GROUP_CONSUMPTION, $analyzer->getGroup());
    }

    public function testCanThrowIgnoreEventIfWeDontHaveInConsumptionUnitPrice()
    {
        $deliveryPoint = new DeliveryPoint();

        $consumption = new InvoiceConsumption();

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);

        $service = $this->createMock(PricingManager::class);
        $analyzer = $this->getAnalyzerMock(UnitPriceAnalyzer::class, [$service, new AmountConversionService()]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('unit_price_missing'), 'consumption.unit_price');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowAnomalyEventIfUnitPriceDoesNotMatch()
    {
        $deliveryPoint = new DeliveryPoint();

        $consumption = new InvoiceConsumption();
        $consumption->setUnitPrice(6*10**5);
        $consumption->setIndexStartedAt($startedAt = new \DateTime('2018-03-01'));
        $consumption->setIndexFinishedAt($finishedAt = new \DateTime('2018-09-01'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);

        $pricing1 = new Pricing();
        $pricing1->setConsumptionBasePrice(12*10**5);
        $pricing2 = new Pricing();
        $pricing2->setConsumptionBasePrice(24*10**5);

        $service = $this->createMock(PricingManager::class);
        $service->expects($this->once())->method('getPricingsBetweenInterval')
            ->with($deliveryPoint, $consumption->getIndexStartedAt(), $consumption->getIndexFinishedAt())
            ->willReturn([$pricing1, $pricing2]);

        $analyzer = $this->getAnalyzerMock(UnitPriceAnalyzer::class, [$service, new AmountConversionService()]);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Anomaly::TYPE_CONSUMPTION,
            transInfo('unit_price_incorrect'),
            transInfo('consumption_unit_price_applied_rules', [
                'from' => $startedAt,
                'to' => $finishedAt,
                'minimum_base_price' => 0.12,
                'maximum_base_price' => 0.24
            ]), '6,000c€', null,
            transInfo('expected_value_between_x_y', ['x' => '12,000c€', 'y' => '24,000c€']),
            'consumption.unit_price'
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfNoPricingFound()
    {
        $deliveryPoint = new DeliveryPoint();

        $consumption = new InvoiceConsumption();
        $consumption->setUnitPrice(9);
        $consumption->setIndexStartedAt($startedAt = new \DateTime('2016-03-01'));
        $consumption->setIndexFinishedAt($finishedAt = new \DateTime('2016-09-01'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);

        $service = $this->createMock(PricingManager::class);
        $service->expects($this->once())->method('getPricingsBetweenInterval')
            ->with($deliveryPoint, $consumption->getIndexStartedAt(), $consumption->getIndexFinishedAt())
            ->willReturn([]);

        $analyzer = $this->getAnalyzerMock(UnitPriceAnalyzer::class, [$service, new AmountConversionService()]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('no_pricing_found_in_period', ['from' => $startedAt->format(DateFormatService::ANALYZER), 'to' => $finishedAt->format(DateFormatService::ANALYZER)]));
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanNotThrowIfDataMatch()
    {
        $deliveryPoint = new DeliveryPoint();

        $consumption = new InvoiceConsumption();
        $consumption->setUnitPrice(6*10**5);
        $consumption->setIndexStartedAt($startedAt = new \DateTime('2018-03-01'));
        $consumption->setIndexFinishedAt($finishedAt = new \DateTime('2018-09-01'));

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);
        $deliveryPointInvoice->setConsumption($consumption);

        $pricing1 = new Pricing();
        $pricing1->setConsumptionBasePrice(5*10**5);
        $pricing2 = new Pricing();
        $pricing2->setConsumptionBasePrice(7*10**5);

        $service = $this->createMock(PricingManager::class);
        $service->expects($this->once())->method('getPricingsBetweenInterval')
            ->with($deliveryPoint, $consumption->getIndexStartedAt(), $consumption->getIndexFinishedAt())
            ->willReturn([$pricing1, $pricing2]);

        $analyzer = $this->getAnalyzerMock(UnitPriceAnalyzer::class, [$service, new AmountConversionService()]);
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->analyze($deliveryPointInvoice);
    }
}