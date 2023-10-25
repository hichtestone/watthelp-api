<?php

declare(strict_types=1);

namespace App\Tests\Unit\Analyzer\DeliveryPointInvoice\Consumption;

use App\Analyzer\AnalyzerInterface;
use App\Analyzer\DeliveryPointInvoice\Consumption\TotalTaxExcludedAnalyzer;
use App\Entity\DeliveryPoint;
use App\Entity\Invoice\Anomaly;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Entity\Invoice\InvoiceConsumption;
use App\Entity\Pricing;
use App\Entity\Rate;
use App\Exceptions\IgnoreException;
use App\Manager\PricingManager;
use App\Manager\TranslationManager;
use App\Model\AmountDiff;
use App\Model\TranslationInfo;
use App\Service\AmountConversionService;
use App\Service\ConsumptionService;
use App\Service\LogService;
use App\Tests\WebTestCase;

/**
 * @group unit
 * @group analyzer
 * @group analyzer-consumption
 * @group analyzer-consumption-total-tax-excluded
 */
class TotalTaxExcludedAnalyzerTest extends WebTestCase
{
    public function testCanGetAnalyzerName()
    {
        $translationManager = $this->createMock(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $manager = $this->createMock(PricingManager::class);
        $conversionService = new AmountConversionService();
        $service = $this->createMock(ConsumptionService::class);

        $analyzer = new TotalTaxExcludedAnalyzer($translationManager, $logger, $service, $manager, $conversionService);

        $this->assertEquals('delivery_point_invoice.consumption.total_tax_excluded', $analyzer->getName());
    }

    public function testCanGetAnalyzerGroup()
    {
        $translationManager = $this->createMock(TranslationManager::class);
        $logger = $this->createMock(LogService::class);
        $manager = $this->createMock(PricingManager::class);
        $conversionService = new AmountConversionService();
        $service = $this->createMock(ConsumptionService::class);

        $analyzer = new TotalTaxExcludedAnalyzer($translationManager, $logger, $service, $manager, $conversionService);

        $this->assertEquals(AnalyzerInterface::GROUP_CONSUMPTION, $analyzer->getGroup());
    }

    public function testCanThrowIgnoreEventIfWeDontHaveTotalOnConsumption()
    {
        $consumption = new InvoiceConsumption();

        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);

        $manager = $this->createMock(PricingManager::class);
        $conversionService = new AmountConversionService();
        $service = $this->createMock(ConsumptionService::class);

        $analyzer = $this->getAnalyzerMock(TotalTaxExcludedAnalyzer::class, [$service, $manager, $conversionService]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('ht_amount_missing'), 'consumption.total');
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowIgnoreEventIfConsumptionServiceThrowException()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setTotal(100*10**7); // 100€
        $consumption->setIndexStartedAt($startedAt = new \DateTime('2018-03-01'));
        $consumption->setIndexFinishedAt($finishedAt = new \DateTime('2018-09-01'));

        $deliveryPoint = new DeliveryPoint();
        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);

        $pricing = new Pricing();
        $pricing->setConsumptionBasePrice((int) (6.810*10**5)); // 6,810 c€/kWh

        $manager = $this->createMock(PricingManager::class);
        $manager->expects($this->once())->method('getPricingsBetweenInterval')
            ->with($deliveryPoint, $consumption->getIndexStartedAt(), $consumption->getIndexFinishedAt())
            ->willReturn([$pricing]);
        $conversionService = new AmountConversionService();
        $service = $this->createMock(ConsumptionService::class);
        $service->expects($this->once())->method('getConsumptionQuantity')
            ->with($consumption)
            ->willThrowException(new IgnoreException(transInfo('Test')));

        $analyzer = $this->getAnalyzerMock(TotalTaxExcludedAnalyzer::class, [$service, $manager, $conversionService]);
        $analyzer->expects($this->once())->method('ignore')->with(transInfo('Test'));
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanThrowAnomalyEventIfTotalDoesNotMatch()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setTotal(100*10**7); // 100€
        $consumption->setIndexStartedAt($startedAt = new \DateTime('2018-03-01'));
        $consumption->setIndexFinishedAt($finishedAt = new \DateTime('2018-09-01'));

        $deliveryPoint = new DeliveryPoint();
        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);

        $pricing = new Pricing();
        $pricing->setConsumptionBasePrice((int) (6.810*10**5)); // 6,810 c€/kWh

        $manager = $this->createMock(PricingManager::class);
        $manager->expects($this->once())->method('getPricingsBetweenInterval')
            ->with($deliveryPoint, $consumption->getIndexStartedAt(), $consumption->getIndexFinishedAt())
            ->willReturn([$pricing]);
        $conversionService = new AmountConversionService();
        $service = $this->createMock(ConsumptionService::class);
        $service->expects($this->once())->method('getConsumptionQuantity')
            ->with($consumption)
            ->willReturn(1200);

        $appliedRules = transInfo('consumption_total_tax_excluded_applied_rules', [
            'from' => $startedAt,
            'to' => $finishedAt,
            'minimum_base_price' => 0.0681,
            'maximum_base_price' => 0.0681,
            'finish_index' => 0,
            'start_index' => 0,
            'consumption' => 1200,
            'margin' => 0.1,
            'minimum' => 81.62,
            'maximum' => 81.82
        ]);

        $analyzer = $this->getAnalyzerMock(TotalTaxExcludedAnalyzer::class, [$service, $manager, $conversionService]);
        $analyzer->expects($this->once())->method('anomaly')->with(
            Anomaly::TYPE_CONSUMPTION,
            transInfo('amount_incorrect', ['amount_type' => 'HT', 'type' => 'consumption']),
            $appliedRules, '100,00€', null, transInfo('expected_value_between_x_y', ['x' => '81,62€', 'y' => '81,82€']),
            'consumption.total', new AmountDiff(181800000, 22.22, Anomaly::PROFIT_PROVIDER)
        );
        $analyzer->analyze($deliveryPointInvoice);
    }

    public function testCanNotThrowIfDataMatch()
    {
        $consumption = new InvoiceConsumption();
        $consumption->setTotal((int) (68.10*10**7)); // 68,10€
        $consumption->setIndexStartedAt($startedAt = new \DateTime('2018-03-01'));
        $consumption->setIndexFinishedAt($finishedAt = new \DateTime('2018-09-01'));

        $deliveryPoint = new DeliveryPoint();
        $deliveryPointInvoice = new DeliveryPointInvoice();
        $deliveryPointInvoice->setConsumption($consumption);
        $deliveryPointInvoice->setDeliveryPoint($deliveryPoint);

        $pricing = new Pricing();
        $pricing->setConsumptionBasePrice((int) (6.810*10**5)); // 6,810 c€/kWh

        $manager = $this->createMock(PricingManager::class);
        $manager->expects($this->once())->method('getPricingsBetweenInterval')
            ->with($deliveryPoint, $consumption->getIndexStartedAt(), $consumption->getIndexFinishedAt())
            ->willReturn([$pricing]);
        $conversionService = new AmountConversionService();
        $service = $this->createMock(ConsumptionService::class);
        $service->expects($this->once())->method('getConsumptionQuantity')
            ->with($consumption)
            ->willReturn(1000);

        $analyzer = $this->getAnalyzerMock(TotalTaxExcludedAnalyzer::class, [$service, $manager, $conversionService]);
        $analyzer->expects($this->never())->method('anomaly');
        $analyzer->expects($this->never())->method('ignore');
        $analyzer->analyze($deliveryPointInvoice);
    }
}